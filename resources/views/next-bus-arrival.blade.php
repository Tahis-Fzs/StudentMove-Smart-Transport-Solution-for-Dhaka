<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="/css/notification.css">
    <link rel="stylesheet" href="/css/next-bus-arrival.css">
    {{-- Leaflet CSS (local to avoid SRI/network issues) --}}
    <link rel="stylesheet" href="/css/leaflet-bundle.css?v=1.9.4">
    @endpush

    <div class="nba-container">
    <!-- Day Tabs -->
    <div class="nba-day-tabs">
        @foreach(['sat','sun','mon','tue','wed','thu'] as $day)
            <button type="button" data-day="{{ $day }}" onclick="switchDay('{{ $day }}', this)">{{ strtoupper($day) }}</button>
        @endforeach
    </div>
    <div class="nba-tabs-underline"></div>

    <div class="nba-info" id="nba-info">{{ $scheduleCards->count() }} schedules available</div>

    <div class="nba-schedules" id="nba-schedules">
        @foreach($scheduleCards as $index => $card)
            @include('partials.nba-schedule-card', [
                'card' => $card,
                'scheduleId' => $index + 1,
            ])
        @endforeach
    </div>

    <!-- Toast Notification for Delay -->
    <div id="toast-notification" style="display:none; position:fixed; top:80px; left:50%; transform:translateX(-50%); background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color:white; padding:20px 24px; border-radius:12px; box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4); z-index:10000; min-width:400px; max-width:90%; animation:slideDown 0.3s ease-out;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="font-size:24px;">⚠️</div>
            <div style="flex:1;">
                <div style="font-weight:800; font-size:18px; margin-bottom:4px;">DELAY ALERT</div>
                <div id="toast-msg" style="font-size:14px; opacity:0.95;">Bus is late!</div>
            </div>
            <button onclick="document.getElementById('toast-notification').style.display='none'" style="background:rgba(255,255,255,0.2); border:none; color:white; width:24px; height:24px; border-radius:50%; cursor:pointer; font-size:16px; line-height:1; padding:0;">×</button>
        </div>
    </div>
    
    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        #toast-notification {
            transition: all 0.3s ease-out;
        }
    </style>
    
    <!-- Download Button -->
    <button class="nba-download-btn" onclick="downloadPDF()">
        <i class="bi bi-file-earmark-pdf"></i> Download PDF
    </button>
    </div>

    @push('scripts')
    <script>
        function switchDay(day, btn) {
            document.querySelectorAll('.nba-day-tabs button').forEach(b => b.classList.remove('active'));
            (btn || event.target).classList.add('active');

            let visible = 0;
            document.querySelectorAll('.nba-schedule-card').forEach(card => {
                let days = [];
                try {
                    days = JSON.parse(card.dataset.runDays || '[]');
                } catch (e) {
                    days = [];
                }
                const show = !days.length || days.includes(day);
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            const info = document.getElementById('nba-info');
            if (info) {
                info.textContent = visible + ' schedule' + (visible === 1 ? '' : 's')
                    + ' available · ' + day.toUpperCase();
            }
        }

        function initDayTabs() {
            const map = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            const supported = ['sat', 'sun', 'mon', 'tue', 'wed', 'thu'];
            const today = map[new Date().getDay()];
            const day = supported.includes(today) ? today : 'sun';
            const btn = document.querySelector('.nba-day-tabs button[data-day="' + day + '"]');
            if (btn) switchDay(day, btn);
        }

        function downloadPDF() {
            const link = document.createElement('a');
            link.href = '{{ asset("pdf/bus-schedule.pdf") }}';
            link.download = 'bus-schedule.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
    {{-- Leaflet 1.9.4 (real library — stub previously broke markers / simulation) --}}
    <script src="/js/leaflet-bundle.js?v=1.9.4"></script>
    <script>
        const scheduleMaps = {};
        const scheduleMarkers = {};
        const scheduleBusIds = {};
        const schedulePollTimers = {};
        const scheduleEventSources = {};
        const scheduleStreamActive = {};
        const scheduleLastPositions = {};
        const scheduleLastDelays = {};
        const scheduleNotificationTimeouts = {};
        const scheduleAnimFrames = {};
        const scheduleHeadings = {};
        const scheduleSimTimers = {};
        const manualOverride = {};
        const POLL_MS = 1000;
        const ETA_POLL_MS = 5000;
        const STREAM_RECONNECT_MS = 3000;
        const SMOOTH_MS = 900;
        const LIVE_GPS_MAX_AGE = 120;
        const schedulePaths = {
            1: [
                { lat: 23.8103, lng: 90.4125 },
                { lat: 23.8130, lng: 90.4180 },
                { lat: 23.8170, lng: 90.4250 },
                { lat: 23.8205, lng: 90.4300 },
            ],
            2: [
                { lat: 23.8150, lng: 90.4100 },
                { lat: 23.8180, lng: 90.4150 },
                { lat: 23.8220, lng: 90.4200 },
                { lat: 23.8250, lng: 90.4250 },
            ],
            3: [
                { lat: 23.8050, lng: 90.4000 },
                { lat: 23.8080, lng: 90.4050 },
                { lat: 23.8120, lng: 90.4100 },
                { lat: 23.8150, lng: 90.4150 },
            ]
        };

        function busIcon(headingDeg) {
            const rot = headingDeg != null && !Number.isNaN(headingDeg) ? headingDeg : 0;
            if (typeof L === 'undefined' || typeof L.divIcon !== 'function') return null;
            return L.divIcon({
                className: 'bus-marker-wrap',
                html: `<div class="bus-marker" style="transform:rotate(${rot}deg)" title="Heading ${Math.round(rot)}°"><span class="bus-marker__arrow"></span></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
            });
        }

        function ensureMap(scheduleId, center) {
            const mapEl = document.getElementById(`map-${scheduleId}`);
            const loadingEl = document.getElementById(`map-loading-${scheduleId}`);
            if (!mapEl || typeof L === 'undefined') return null;

            let map = scheduleMaps[scheduleId];
            if (!map) {
                if (mapEl._leaflet_id) {
                    mapEl._leaflet_id = null;
                    mapEl.innerHTML = '';
                    if (loadingEl) {
                        mapEl.appendChild(loadingEl);
                        loadingEl.style.display = '';
                    }
                }
                const view = center || schedulePaths[scheduleId]?.[0] || { lat: 23.8103, lng: 90.4125 };
                const latlng = Array.isArray(view) ? view : [view.lat, view.lng];
                map = L.map(mapEl, { preferCanvas: true }).setView(latlng, 13);
                scheduleMaps[scheduleId] = map;
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
            }

            let marker = scheduleMarkers[scheduleId];
            if (!marker) {
                const c = map.getCenter();
                const icon = busIcon(scheduleHeadings[scheduleId] || 0);
                marker = icon
                    ? L.marker([c.lat, c.lng], { icon }).addTo(map)
                    : L.marker([c.lat, c.lng]).addTo(map);
                scheduleMarkers[scheduleId] = marker;
            }

            if (loadingEl) loadingEl.style.display = 'none';
            requestAnimationFrame(() => {
                if (map.invalidateSize) map.invalidateSize();
            });

            return { map, marker };
        }

        function resetPollInterval(scheduleId, busId) {
            if (schedulePollTimers[scheduleId]) {
                clearInterval(schedulePollTimers[scheduleId]);
            }
            const ms = scheduleStreamActive[scheduleId] ? ETA_POLL_MS : POLL_MS;
            schedulePollTimers[scheduleId] = setInterval(
                () => fetchLiveLocation(scheduleId, busId),
                ms
            );
        }

        function disconnectLiveStream(scheduleId) {
            if (scheduleEventSources[scheduleId]) {
                scheduleEventSources[scheduleId].close();
                delete scheduleEventSources[scheduleId];
            }
            scheduleStreamActive[scheduleId] = false;
        }

        function connectLiveStream(scheduleId, busId, enabled) {
            disconnectLiveStream(scheduleId);

            if (!enabled || !busId || Number.isNaN(Number(busId)) || typeof EventSource === 'undefined') {
                return;
            }

            const es = new EventSource(`/api/bus/stream/${busId}`);
            scheduleEventSources[scheduleId] = es;

            es.addEventListener('location', (ev) => {
                try {
                    applyStreamGps(scheduleId, JSON.parse(ev.data));
                } catch (e) {
                    /* ignore malformed payloads */
                }
            });

            es.onopen = () => {
                scheduleStreamActive[scheduleId] = true;
                resetPollInterval(scheduleId, busId);
            };

            es.onerror = () => {
                scheduleStreamActive[scheduleId] = false;
                es.close();
                delete scheduleEventSources[scheduleId];
                resetPollInterval(scheduleId, busId);
                setTimeout(() => connectLiveStream(scheduleId, busId, enabled), STREAM_RECONNECT_MS);
            };
        }

        function applyStreamGps(scheduleId, data) {
            if (manualOverride[scheduleId]) return;

            const lat = data.lat != null ? parseFloat(data.lat) : null;
            const lng = data.lng != null ? parseFloat(data.lng) : null;
            if (lat == null || lng == null || Number.isNaN(lat) || Number.isNaN(lng)) return;

            const heading = data.heading != null ? Number(data.heading) : null;
            const speed = data.speed_kmh != null ? Number(data.speed_kmh) : null;
            const age = data.gps_age_seconds != null ? Number(data.gps_age_seconds) : 0;

            updateGpsBadge(scheduleId, {
                has_gps: true,
                gps_fresh: true,
                gps_stale: false,
                gps_age_seconds: age,
                stream: true,
            });
            updateMotionBadge(scheduleId, {
                heading,
                speed_kmh: speed,
                has_gps: true,
                source: 'live',
            });

            const statusBadge = document.getElementById(`status-badge-${scheduleId}`);
            const etaTime = document.getElementById(`eta-time-${scheduleId}`);
            const etaText = etaTime ? etaTime.textContent : '—';
            const statusMsg = statusBadge ? statusBadge.textContent : 'On Time';
            const isDelayed = statusMsg.toLowerCase().includes('delay');

            updateMarker(
                scheduleId,
                lat,
                lng,
                isDelayed ? 'delayed' : 'on_time',
                etaText,
                null,
                statusMsg,
                isDelayed,
                0,
                heading,
                speed
            );
        }

        function initMap(scheduleId, center, busId, streamEnabled) {
            scheduleBusIds[scheduleId] = busId;
            const linkEl = document.getElementById(`bus-link-${scheduleId}`);
            if (linkEl) {
                if (busId && streamEnabled) {
                    linkEl.textContent = 'Linked driver bus #' + busId + ' · live GPS stream + ETA poll';
                } else if (busId) {
                    linkEl.textContent = 'Linked bus #' + busId + ' · ETA poll (demo path if no GPS)';
                } else {
                    linkEl.textContent = 'Demo route · simulated path until a bus is linked';
                }
            }

            const ready = ensureMap(scheduleId, center);
            if (!ready) {
                const loadingEl = document.getElementById(`map-loading-${scheduleId}`);
                if (loadingEl) loadingEl.textContent = 'Error: Map library failed to load.';
                return;
            }

            connectLiveStream(scheduleId, busId, !!streamEnabled);
            resetPollInterval(scheduleId, busId);
            fetchLiveLocation(scheduleId, busId);
        }

        async function fetchLiveLocation(scheduleId, busId) {
            if (manualOverride[scheduleId]) return;

            try {
                const res = await fetch(`/api/bus/get-location/${busId}`, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();

                const {
                    lat, lng, status, eta_text, delay_msg, is_delayed, status_msg, delay_minutes,
                    has_gps, gps_fresh, gps_stale, gps_age_seconds, heading, speed_kmh
                } = data;

                const ageOk = gps_age_seconds == null || gps_age_seconds <= LIVE_GPS_MAX_AGE;
                const useLive = !!has_gps && ageOk && lat != null && lng != null
                    && !Number.isNaN(parseFloat(lat)) && !Number.isNaN(parseFloat(lng));

                if (useLive) {
                    updateGpsBadge(scheduleId, { has_gps: true, gps_fresh, gps_stale, gps_age_seconds });
                    updateMotionBadge(scheduleId, {
                        heading: heading != null ? Number(heading) : null,
                        speed_kmh: speed_kmh != null ? Number(speed_kmh) : null,
                        has_gps: true,
                        source: gps_fresh ? 'live' : 'recent',
                    });
                    updateMarker(
                        scheduleId,
                        parseFloat(lat),
                        parseFloat(lng),
                        status || (is_delayed ? 'delayed' : 'on_time'),
                        eta_text,
                        delay_msg,
                        status_msg,
                        is_delayed,
                        delay_minutes,
                        heading != null ? Number(heading) : null,
                        speed_kmh != null ? Number(speed_kmh) : null
                    );
                    return;
                }

                updateGpsBadge(scheduleId, {
                    has_gps: !!has_gps,
                    gps_fresh: false,
                    gps_stale: !!has_gps,
                    gps_age_seconds: gps_age_seconds,
                    offline: !has_gps,
                });
                runSimulatedPath(scheduleId);
            } catch (error) {
                if (manualOverride[scheduleId]) return;
                updateGpsBadge(scheduleId, {
                    has_gps: false,
                    gps_fresh: false,
                    gps_stale: false,
                    gps_age_seconds: null,
                    offline: true,
                });
                runSimulatedPath(scheduleId);
            }
        }

        function compassLabel(deg) {
            if (deg == null || Number.isNaN(Number(deg))) return '—';
            const n = Number(deg);
            const dirs = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
            const i = Math.round((((n % 360) + 360) % 360) / 45) % 8;
            return dirs[i] + ' ' + Math.round(n) + '°';
        }

        function updateMotionBadge(scheduleId, info) {
            const el = document.getElementById(`motion-badge-${scheduleId}`);
            if (!el) return;

            const heading = info.heading != null && !Number.isNaN(Number(info.heading))
                ? Number(info.heading) : null;
            const speed = info.speed_kmh != null && !Number.isNaN(Number(info.speed_kmh))
                ? Number(info.speed_kmh) : null;

            el.classList.remove('is-live', 'is-demo', 'is-idle');

            if (heading == null && speed == null) {
                el.classList.add('is-idle');
                el.textContent = 'Heading — · Speed —';
                return;
            }

            el.classList.add(info.source === 'live' || info.has_gps ? 'is-live' : 'is-demo');
            const speedLabel = speed != null ? speed.toFixed(0) + ' km/h' : '—';
            el.textContent = 'Heading ' + compassLabel(heading) + ' · Speed ' + speedLabel;
        }

        function setMarkerHeading(scheduleId, heading) {
            const marker = scheduleMarkers[scheduleId];
            if (!marker) return;
            if (heading == null || Number.isNaN(heading)) return;
            scheduleHeadings[scheduleId] = heading;
            const icon = busIcon(heading);
            if (icon && typeof marker.setIcon === 'function') {
                marker.setIcon(icon);
            }
        }

        function updateGpsBadge(scheduleId, info) {
            const el = document.getElementById(`gps-badge-${scheduleId}`);
            if (!el) return;
            el.classList.remove('gps-live-badge--live', 'gps-live-badge--stale', 'gps-live-badge--idle', 'gps-live-badge--offline');

            if (info.offline) {
                el.classList.add('gps-live-badge--offline');
                el.textContent = 'GPS: offline (demo path)';
                return;
            }
            if (info.gps_fresh) {
                el.classList.add('gps-live-badge--live');
                el.textContent = info.stream
                    ? 'GPS: live stream · ' + (info.gps_age_seconds ?? 0) + 's ago'
                    : 'GPS: live · ' + (info.gps_age_seconds ?? 0) + 's ago';
                return;
            }
            if (info.gps_stale || info.has_gps) {
                el.classList.add('gps-live-badge--stale');
                const age = info.gps_age_seconds != null ? info.gps_age_seconds + 's ago' : 'stale';
                el.textContent = 'GPS: stale · last ' + age;
                return;
            }
            el.classList.add('gps-live-badge--idle');
            el.textContent = 'GPS: waiting for driver';
        }

        function runSimulatedPath(scheduleId) {
            ensureMap(scheduleId);
            const path = schedulePaths[scheduleId] || schedulePaths[1];
            const timeMs = Date.now();
            const cycleTime = 8000;
            const progress = (timeMs % cycleTime) / cycleTime;
            const segmentIndex = Math.floor(progress * (path.length - 1));
            const segmentProgress = (progress * (path.length - 1)) % 1;

            const currentPoint = path[segmentIndex];
            const nextPoint = path[Math.min(segmentIndex + 1, path.length - 1)];
            const lat = currentPoint.lat + (nextPoint.lat - currentPoint.lat) * segmentProgress;
            const lng = currentPoint.lng + (nextPoint.lng - currentPoint.lng) * segmentProgress;

            const etas = { 1: [10, 8, 6, 4], 2: [15, 12, 9, 6], 3: [8, 6, 4, 2] };
            const etaValues = etas[scheduleId] || [10, 8, 6, 4];
            const etaIndex = Math.floor(progress * (etaValues.length - 1));
            const eta = Math.max(0, Math.ceil(etaValues[etaIndex] * (1 - segmentProgress)));

            const isDelayed = segmentProgress > 0.6 && segmentIndex === path.length - 1;
            const simulatedDelayMinutes = isDelayed ? Math.floor(5 + segmentProgress * 10) : 0;

            const dLat = nextPoint.lat - currentPoint.lat;
            const dLng = nextPoint.lng - currentPoint.lng;
            const simHeading = (Math.atan2(dLng, dLat) * 180 / Math.PI + 360) % 360;
            const simSpeed = 28 + segmentProgress * 8;

            updateMotionBadge(scheduleId, {
                heading: simHeading,
                speed_kmh: simSpeed,
                has_gps: false,
                source: 'demo',
            });
            updateMarker(
                scheduleId, lat, lng,
                isDelayed ? 'delayed' : 'on_time',
                `${eta} mins`,
                isDelayed ? `Simulated delay: ${simulatedDelayMinutes} minutes` : null,
                isDelayed ? 'Delayed' : 'On Time',
                isDelayed, simulatedDelayMinutes, simHeading, simSpeed
            );
        }

        function easeInOutCubic(t) {
            return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
        }

        function animateMarkerTo(scheduleId, lat, lng) {
            ensureMap(scheduleId);
            const map = scheduleMaps[scheduleId];
            const marker = scheduleMarkers[scheduleId];
            if (!map || !marker) return;

            if (scheduleAnimFrames[scheduleId]) {
                cancelAnimationFrame(scheduleAnimFrames[scheduleId]);
                scheduleAnimFrames[scheduleId] = null;
            }

            const from = marker.getLatLng();
            const dLat = Math.abs(from.lat - lat);
            const dLng = Math.abs(from.lng - lng);
            if (dLat < 0.00001 && dLng < 0.00001) {
                marker.setLatLng([lat, lng]);
                scheduleLastPositions[scheduleId] = { lat, lng };
                return;
            }

            const start = performance.now();
            const fromLat = from.lat;
            const fromLng = from.lng;
            const shouldPan = dLat > 0.00035 || dLng > 0.00035;

            function step(now) {
                const t = Math.min(1, (now - start) / SMOOTH_MS);
                const e = easeInOutCubic(t);
                const curLat = fromLat + (lat - fromLat) * e;
                const curLng = fromLng + (lng - fromLng) * e;
                marker.setLatLng([curLat, curLng]);

                if (t < 1) {
                    scheduleAnimFrames[scheduleId] = requestAnimationFrame(step);
                } else {
                    scheduleAnimFrames[scheduleId] = null;
                    scheduleLastPositions[scheduleId] = { lat, lng };
                    if (shouldPan) {
                        map.panTo([lat, lng], { animate: true, duration: 0.4 });
                    }
                }
            }

            scheduleAnimFrames[scheduleId] = requestAnimationFrame(step);
        }

        function updateMarker(scheduleId, lat, lng, status, etaText, delayMsg, statusMsg, isDelayedFlag, delayMinutes = 0, heading = null, speedKmh = null) {
            ensureMap(scheduleId, [lat, lng]);
            const map = scheduleMaps[scheduleId];
            const marker = scheduleMarkers[scheduleId];
            if (!map || !marker) return;

            const lastPos = scheduleLastPositions[scheduleId];
            const lastDelay = scheduleLastDelays[scheduleId];
            const delayChanged = lastDelay !== isDelayedFlag ||
                (isDelayedFlag && lastDelay && Math.abs((lastDelay.delayMinutes || 0) - delayMinutes) >= 1);

            let h = heading != null && !Number.isNaN(Number(heading)) ? Number(heading) : null;
            if (h == null && lastPos) {
                const moved = Math.abs(lastPos.lat - lat) > 0.00002 || Math.abs(lastPos.lng - lng) > 0.00002;
                if (moved) {
                    h = (Math.atan2(lng - lastPos.lng, lat - lastPos.lat) * 180 / Math.PI + 360) % 360;
                }
            }
            if (h != null) setMarkerHeading(scheduleId, h);

            animateMarkerTo(scheduleId, lat, lng);

            scheduleLastPositions[scheduleId] = { lat, lng };
            scheduleLastDelays[scheduleId] = { isDelayed: isDelayedFlag, delayMinutes };

            const statusBadge = document.getElementById(`status-badge-${scheduleId}`);
            const etaTime = document.getElementById(`eta-time-${scheduleId}`);
            const delay = (status && status.toLowerCase() === 'delayed') || isDelayedFlag;

            if (statusBadge) {
                statusBadge.style.background = delay ? '#fde2e2' : '#d4edda';
                statusBadge.style.color = delay ? '#b91c1c' : '#155724';
                statusBadge.textContent = delay ? (statusMsg || `Delayed by ${delayMinutes} min`) : 'On Time';
            }
            if (etaTime) {
                etaTime.textContent = delay ? `${delayMinutes} min delay` : (etaText || '10 mins');
            }

            const toast = document.getElementById('toast-notification');
            const toastMsgEl = document.getElementById('toast-msg');

            if (delay) {
                const displayMsg = delayMsg || `Schedule ${scheduleId} is delayed by ${delayMinutes} minute${delayMinutes !== 1 ? 's' : ''}.`;
                if (scheduleNotificationTimeouts[scheduleId]) {
                    clearTimeout(scheduleNotificationTimeouts[scheduleId]);
                }
                if (toast) {
                    toast.style.display = 'block';
                    toast.style.visibility = 'visible';
                    toast.style.opacity = '1';
                    if (toastMsgEl) toastMsgEl.textContent = displayMsg;
                }
                scheduleNotificationTimeouts[scheduleId] = setTimeout(() => {
                    if (toast) toast.style.display = 'none';
                }, 5000);
            } else if (delayChanged && toast) {
                toast.style.display = 'none';
            }
        }

        function startSimulation(scheduleId) {
            const ready = ensureMap(scheduleId);
            if (!ready) {
                alert('Map library failed to load. Hard-refresh the page (Cmd+Shift+R).');
                return;
            }
            const { map, marker } = ready;

            manualOverride[scheduleId] = true;
            if (scheduleSimTimers[scheduleId]) {
                clearInterval(scheduleSimTimers[scheduleId]);
                scheduleSimTimers[scheduleId] = null;
            }

            const path = schedulePaths[scheduleId] || schedulePaths[1];
            const statusBadge = document.getElementById(`status-badge-${scheduleId}`);
            const etaTime = document.getElementById(`eta-time-${scheduleId}`);
            const etas = { 1: [10, 8, 6, 4], 2: [15, 12, 9, 6], 3: [8, 6, 4, 2] };
            const etaValues = etas[scheduleId] || [10, 8, 6, 4];

            let idx = 0;
            const tick = () => {
                if (idx >= path.length) {
                    clearInterval(scheduleSimTimers[scheduleId]);
                    scheduleSimTimers[scheduleId] = null;
                    if (statusBadge) {
                        statusBadge.textContent = 'Arrived';
                        statusBadge.style.background = '#e0f2fe';
                        statusBadge.style.color = '#0f172a';
                    }
                    if (etaTime) etaTime.textContent = '0 mins';
                    updateMotionBadge(scheduleId, { heading: null, speed_kmh: 0, source: 'demo' });
                    setTimeout(() => { manualOverride[scheduleId] = false; }, 1500);
                    return;
                }

                const pos = path[idx];
                const next = path[Math.min(idx + 1, path.length - 1)];
                const heading = (Math.atan2(next.lng - pos.lng, next.lat - pos.lat) * 180 / Math.PI + 360) % 360;
                const speed = 30 + idx * 2;

                updateMotionBadge(scheduleId, { heading, speed_kmh: speed, source: 'demo' });
                updateGpsBadge(scheduleId, {
                    has_gps: false, gps_fresh: false, gps_stale: false,
                    gps_age_seconds: null, offline: true,
                });
                setMarkerHeading(scheduleId, heading);
                marker.setLatLng([pos.lat, pos.lng]);
                map.panTo([pos.lat, pos.lng]);
                if (etaTime) etaTime.textContent = `${etaValues[idx] || 0} mins`;
                if (statusBadge) {
                    statusBadge.textContent = 'Simulating';
                    statusBadge.style.background = '#e0f2fe';
                    statusBadge.style.color = '#0f172a';
                }
                idx++;
            };

            tick();
            scheduleSimTimers[scheduleId] = setInterval(tick, 1200);
        }

        function triggerDelay(scheduleId) {
            const ready = ensureMap(scheduleId);
            if (!ready) {
                alert('Map library failed to load. Hard-refresh the page (Cmd+Shift+R).');
                return;
            }
            const { marker } = ready;

            manualOverride[scheduleId] = true;
            const pos = marker.getLatLng();
            const delayMsg = `Traffic congestion detected on Schedule ${scheduleId}. Bus delayed by 5 minutes.`;
            updateMotionBadge(scheduleId, {
                heading: scheduleHeadings[scheduleId] ?? 0,
                speed_kmh: 8,
                source: 'demo',
            });
            updateMarker(scheduleId, pos.lat, pos.lng, 'delayed', 'Delayed', delayMsg, 'Delayed by 5 min', true, 5);
            setTimeout(() => { manualOverride[scheduleId] = false; }, 8000);
        }

        function testNotification() {
            const toast = document.getElementById('toast-notification');
            const toastMsg = document.getElementById('toast-msg');
            if (toast && toastMsg) {
                toastMsg.textContent = 'Test: Delay alert notification is working!';
                toast.style.display = 'block';
                toast.style.visibility = 'visible';
                toast.style.opacity = '1';
                setTimeout(() => { toast.style.display = 'none'; }, 5000);
            }
        }

        window.startSimulation = startSimulation;
        window.triggerDelay = triggerDelay;
        window.testNotification = testNotification;

        function bootLiveMaps() {
            const buses = @json($scheduleCards->values());
            const defaults = [
                [23.8103, 90.4125],
                [23.8150, 90.4100],
                [23.8050, 90.4000],
            ];

            buses.slice(0, 3).forEach((bus, index) => {
                const scheduleId = index + 1;
                const routeEl = document.getElementById(`route-name-${scheduleId}`);
                if (routeEl && bus.route_name) routeEl.textContent = bus.route_name;

                const center = (bus.current_lat && bus.current_lng)
                    ? [parseFloat(bus.current_lat), parseFloat(bus.current_lng)]
                    : defaults[index];
                setTimeout(
                    () => initMap(scheduleId, center, bus.id, !!bus.live_stream),
                    index * 80
                );
            });

            initDayTabs();

            window.addEventListener('resize', () => {
                Object.values(scheduleMaps).forEach((m) => m && m.invalidateSize && m.invalidateSize());
            });

            window.addEventListener('beforeunload', () => {
                Object.keys(schedulePollTimers).forEach((id) => {
                    if (schedulePollTimers[id]) clearInterval(schedulePollTimers[id]);
                });
                Object.keys(scheduleEventSources).forEach((id) => disconnectLiveStream(id));
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootLiveMaps);
        } else {
            bootLiveMaps();
        }
    </script>
    @endpush
</x-app-layout>