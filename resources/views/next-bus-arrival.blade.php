<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="/css/notification.css">
    <link rel="stylesheet" href="/css/next-bus-arrival.css">
    {{-- Leaflet CSS (local to avoid SRI/network issues) --}}
    <link rel="stylesheet" href="/css/leaflet-bundle.css">
    @endpush

    <!-- Day Tabs -->
    <div class="nba-day-tabs">
        <button onclick="switchDay('sat')">SAT</button>
        <button class="active" onclick="switchDay('sun')">SUN</button>
        <button onclick="switchDay('mon')">MON</button>
        <button onclick="switchDay('tue')">TUE</button>
        <button onclick="switchDay('wed')">WED</button>
        <button onclick="switchDay('thu')">THU</button>
    </div>
    <div class="nba-tabs-underline"></div>

    <!-- Schedule Info -->
    <div class="nba-info">Three Schedule Available</div>
    
    <!-- Schedules -->
    <div class="nba-schedules">
        <!-- Schedule 1: 7.00 AM -->
        <div class="nba-schedule-card">
            <div class="nba-card-title">7.00 AM, 12 May<br><span>from: Rajlakshmi to DSC</span></div>
            
            <!-- Map & Arrival Card -->
            <div id="map-1" class="schedule-map" style="width: 100%; height: 450px; border-radius: 12px; margin-top: 15px; background:#1e293b; border: 1px solid rgba(255,255,255,0.1); position: relative;">
                <div id="map-loading-1" class="map-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #94a3b8; font-size: 14px; z-index: 1000;">Loading map...</div>
            </div>

            <div id="eta-card-1" class="eta-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04)); padding: 15px; border-radius: 10px; margin-top: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.12);">
                <div>
                    <h3 style="margin:0; color:#e5e7eb; font-size: 1.1rem;">Next Bus: <span id="route-name-1">Uttara to DSC</span></h3>
                    <div style="font-size: 0.9rem; margin-top: 5px;">
                        Status: <span id="status-badge-1" style="background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 4px; font-weight: bold;">On Time</span>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.8rem; color: #cbd5e1;">Arriving in</div>
                    <div id="eta-time-1" style="font-size: 1.5rem; font-weight: bold; color: #22c55e;">10 mins</div>
                </div>
            </div>

            <div style="margin-top:10px; display:flex; gap:10px;">
                <button onclick="startSimulation(1)" style="padding:10px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer;">
                    ▶️ Simulate Movement
                </button>
                <button onclick="triggerDelay(1)" style="padding:10px; background:#dc3545; color:white; border:none; border-radius:5px; cursor:pointer;">
                    ⚠️ Simulate Traffic Delay
                </button>
            </div>
        </div>

        <!-- Schedule 2: 8.30 AM -->
        <div class="nba-schedule-card">
            <div class="nba-card-title">8.30 AM, 12 May<br><span>from: Rajlakshmi to Mirpur</span></div>
            
            <!-- Map & Arrival Card -->
            <div id="map-2" class="schedule-map" style="width: 100%; height: 450px; border-radius: 12px; margin-top: 15px; background:#1e293b; border: 1px solid rgba(255,255,255,0.1); position: relative;">
                <div id="map-loading-2" class="map-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #94a3b8; font-size: 14px; z-index: 1000;">Loading map...</div>
            </div>

            <div id="eta-card-2" class="eta-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04)); padding: 15px; border-radius: 10px; margin-top: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.12);">
                <div>
                    <h3 style="margin:0; color:#e5e7eb; font-size: 1.1rem;">Next Bus: <span id="route-name-2">Rajlakshmi to Mirpur</span></h3>
                    <div style="font-size: 0.9rem; margin-top: 5px;">
                        Status: <span id="status-badge-2" style="background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 4px; font-weight: bold;">On Time</span>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.8rem; color: #cbd5e1;">Arriving in</div>
                    <div id="eta-time-2" style="font-size: 1.5rem; font-weight: bold; color: #22c55e;">15 mins</div>
                </div>
            </div>

            <div style="margin-top:10px; display:flex; gap:10px;">
                <button onclick="startSimulation(2)" style="padding:10px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer;">
                    ▶️ Simulate Movement
                </button>
                <button onclick="triggerDelay(2)" style="padding:10px; background:#dc3545; color:white; border:none; border-radius:5px; cursor:pointer;">
                    ⚠️ Simulate Traffic Delay
                </button>
            </div>
        </div>

        <!-- Schedule 3: 12.00 PM -->
        <div class="nba-schedule-card">
            <div class="nba-card-title">12.00 PM, 12 May<br><span>from: Rajlakshmi to Gulshan</span></div>
            
            <!-- Map & Arrival Card -->
            <div id="map-3" class="schedule-map" style="width: 100%; height: 450px; border-radius: 12px; margin-top: 15px; background:#1e293b; border: 1px solid rgba(255,255,255,0.1); position: relative;">
                <div id="map-loading-3" class="map-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #94a3b8; font-size: 14px; z-index: 1000;">Loading map...</div>
            </div>

            <div id="eta-card-3" class="eta-card" style="background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04)); padding: 15px; border-radius: 10px; margin-top: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.12);">
                <div>
                    <h3 style="margin:0; color:#e5e7eb; font-size: 1.1rem;">Next Bus: <span id="route-name-3">Rajlakshmi to Gulshan</span></h3>
                    <div style="font-size: 0.9rem; margin-top: 5px;">
                        Status: <span id="status-badge-3" style="background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 4px; font-weight: bold;">On Time</span>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.8rem; color: #cbd5e1;">Arriving in</div>
                    <div id="eta-time-3" style="font-size: 1.5rem; font-weight: bold; color: #22c55e;">8 mins</div>
                </div>
            </div>

            <div style="margin-top:10px; display:flex; gap:10px;">
                <button onclick="startSimulation(3)" style="padding:10px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer;">
                    ▶️ Simulate Movement
                </button>
                <button onclick="triggerDelay(3)" style="padding:10px; background:#dc3545; color:white; border:none; border-radius:5px; cursor:pointer;">
                    ⚠️ Simulate Traffic Delay
                </button>
            </div>
        </div>
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

    @push('scripts')
    <script>
        function switchDay(day) {
            document.querySelectorAll('.nba-day-tabs button').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
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
    {{-- Leaflet JS (local; avoids SRI errors) --}}
    <script src="/js/leaflet-bundle.js"></script>
    <script>
        // #region agent log
        const dbg = (payload) => {
            const body = JSON.stringify({
                sessionId: 'debug-session',
                runId: 'prefetch',
                hypothesisId: payload.h || 'H1',
                location: payload.loc || 'next-bus-arrival.blade.php',
                message: payload.msg,
                data: payload.data || {},
                timestamp: Date.now()
            });
            const headers = { 'Content-Type': 'application/json' };
            fetch('http://127.0.0.1:7242/ingest/2aff5801-28d0-4d2a-a13f-8cffcaa49a63', {
                method: 'POST',
                headers,
                body
            }).catch(() => {
                // Fallback to local proxy if ingest not reachable
                fetch('/__dbg', { method: 'POST', headers, body }).catch(() => {});
            });
        };
        // capture global errors and rejections
        window.addEventListener('error', (ev) => {
            dbg({ h: 'HE', loc: 'window.error', msg: ev.message || 'error', data: { file: ev.filename, line: ev.lineno, col: ev.colno } });
        });
        window.addEventListener('unhandledrejection', (ev) => {
            dbg({ h: 'HE', loc: 'unhandledrejection', msg: ev.reason ? (ev.reason.message || ev.reason.toString()) : 'rejection' });
        });
        // #endregion

        // Store maps and markers for each schedule
        const scheduleMaps = {};
        const scheduleMarkers = {};
        const scheduleLastPositions = {}; // Track last position for each schedule
        const scheduleLastDelays = {}; // Track last delay state for each schedule
        const scheduleNotificationTimeouts = {}; // Track notification timeouts per schedule
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

        // Initialize all maps
        function initMap(scheduleId, center, busId) {
            dbg({ h: 'H1', loc: `initMap-${scheduleId}`, msg: 'initializing map', data: { scheduleId, center, busId } });
            
            const mapEl = document.getElementById(`map-${scheduleId}`);
            const loadingEl = document.getElementById(`map-loading-${scheduleId}`);
            
            if (!mapEl) {
                dbg({ h: 'H1', loc: `map-element-missing-${scheduleId}`, msg: 'map element not found', data: { scheduleId } });
                return;
            }
            
            if (typeof L === 'undefined') {
                dbg({ h: 'H1', loc: `leaflet-missing-${scheduleId}`, msg: 'window.L undefined' });
                if (loadingEl) loadingEl.textContent = 'Error: Map library failed to load.';
                return;
            }
            
            try {
                const map = L.map(`map-${scheduleId}`).setView(center, 13);
                scheduleMaps[scheduleId] = map;
                
                const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                });
                osm.on('tileerror', (e) => {
                    dbg({ h: 'H1', loc: `tile-error-${scheduleId}`, msg: 'tile load error', data: { scheduleId } });
                });
                osm.on('load', () => {
                    dbg({ h: 'H1', loc: `tile-loaded-${scheduleId}`, msg: 'tiles loaded', data: { scheduleId } });
                    if (loadingEl) loadingEl.style.display = 'none';
                });
                osm.addTo(map);
                
                const marker = L.marker(center).addTo(map);
                scheduleMarkers[scheduleId] = marker;
                
                dbg({ h: 'H1', loc: `map-init-${scheduleId}`, msg: 'map initialized', data: { scheduleId } });
                
                // Hide loading after delay
                setTimeout(() => {
                    if (loadingEl) loadingEl.style.display = 'none';
                }, 1000);
                
                // Start fetching live location for this schedule
                setInterval(() => fetchLiveLocation(scheduleId, busId), 3000);
                fetchLiveLocation(scheduleId, busId); // Initial fetch
            } catch (error) {
                dbg({ h: 'H1', loc: `map-init-error-${scheduleId}`, msg: 'map init failed', data: { error: error.message, scheduleId } });
                if (loadingEl) loadingEl.textContent = 'Error: ' + error.message;
            }
        }

        async function fetchLiveLocation(scheduleId, busId) {
            try {
                const res = await fetch(`/api/bus/get-location/${busId}`);
                dbg({ h: 'H2', loc: `fetch-start-${scheduleId}`, msg: 'fetching bus', data: { scheduleId, busId } });
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();
                
                // Log delay calculation details
                dbg({ 
                    h: 'H2', 
                    loc: `delay-calculation-${scheduleId}`, 
                    msg: 'delay calculated dynamically', 
                    data: { 
                        scheduleId,
                        is_delayed: data.is_delayed,
                        delay_minutes: data.delay_minutes,
                        expected_eta: data.expected_eta,
                        current_eta: data.current_eta,
                        expected_arrival_time: data.expected_arrival_time,
                        actual_arrival_time: data.actual_arrival_time,
                        current_speed: data.current_speed,
                        normal_speed: data.normal_speed,
                        delay_msg: data.delay_msg
                    } 
                });
                
                const { lat, lng, status, eta_text, delay_msg, is_delayed, status_msg, delay_minutes } = data;
                updateMarker(scheduleId, parseFloat(lat), parseFloat(lng), status || (is_delayed ? 'delayed' : 'on_time'), eta_text, delay_msg, status_msg, is_delayed, delay_minutes);
            } catch (error) {
                dbg({ h: 'H2', loc: `fetch-error-${scheduleId}`, msg: 'fetch failed, using simulation', data: { error: error.message, scheduleId } });
                // Use simulated path for this schedule - create smooth movement
                const path = schedulePaths[scheduleId] || schedulePaths[1];
                const timeMs = Date.now();
                const cycleTime = 8000; // 8 second cycle through all points
                const progress = (timeMs % cycleTime) / cycleTime;
                const segmentIndex = Math.floor(progress * (path.length - 1));
                const segmentProgress = (progress * (path.length - 1)) % 1;
                
                // Interpolate between current and next point for smooth movement
                const currentPoint = path[segmentIndex];
                const nextPoint = path[Math.min(segmentIndex + 1, path.length - 1)];
                const lat = currentPoint.lat + (nextPoint.lat - currentPoint.lat) * segmentProgress;
                const lng = currentPoint.lng + (nextPoint.lng - currentPoint.lng) * segmentProgress;
                
                // Calculate ETA based on progress
                const etas = { 1: [10, 8, 6, 4], 2: [15, 12, 9, 6], 3: [8, 6, 4, 2] };
                const etaValues = etas[scheduleId] || [10, 8, 6, 4];
                const etaIndex = Math.floor(progress * (etaValues.length - 1));
                const eta = Math.max(0, Math.ceil(etaValues[etaIndex] * (1 - segmentProgress)));
                
                // Simulate dynamic delay: delay increases as bus gets closer to destination if speed is slow
                const isDelayed = segmentProgress > 0.6 && segmentIndex === path.length - 1;
                const simulatedDelayMinutes = isDelayed ? Math.floor(5 + segmentProgress * 10) : 0;
                
                updateMarker(scheduleId, lat, lng, isDelayed ? 'delayed' : 'on_time', `${eta} mins`, isDelayed ? `Simulated delay: ${simulatedDelayMinutes} minutes` : null, isDelayed ? 'Delayed' : 'On Time', isDelayed, simulatedDelayMinutes);
            }
        }

        function updateMarker(scheduleId, lat, lng, status, etaText, delayMsg, statusMsg, isDelayedFlag, delayMinutes = 0) {
            const map = scheduleMaps[scheduleId];
            const marker = scheduleMarkers[scheduleId];
            if (!map || !marker) {
                dbg({ h: 'H3', loc: `marker-update-fail-${scheduleId}`, msg: 'map or marker missing', data: { scheduleId, hasMap: !!map, hasMarker: !!marker } });
                return;
            }
            
            // Get current marker position
            const currentPos = marker.getLatLng();
            const lastPos = scheduleLastPositions[scheduleId];
            const lastDelay = scheduleLastDelays[scheduleId];
            
            // Check if position actually changed
            const positionChanged = !lastPos || 
                Math.abs(currentPos.lat - lat) > 0.0001 || 
                Math.abs(currentPos.lng - lng) > 0.0001;
            
            // Check if delay status changed
            const delayChanged = lastDelay !== isDelayedFlag || 
                (isDelayedFlag && lastDelay && Math.abs((lastDelay.delayMinutes || 0) - delayMinutes) >= 1);
            
            // Update marker position
            marker.setLatLng([lat, lng]);
            
            // Only pan map if position changed significantly (to avoid constant panning)
            if (positionChanged) {
                map.panTo([lat, lng]);
            }
            
            // Store new position and delay state
            scheduleLastPositions[scheduleId] = { lat, lng };
            scheduleLastDelays[scheduleId] = { isDelayed: isDelayedFlag, delayMinutes };
            
            dbg({ 
                h: 'H3', 
                loc: `marker-update-${scheduleId}`, 
                msg: positionChanged ? 'marker position changed' : 'marker position same', 
                data: { 
                    scheduleId, 
                    newLat: lat, 
                    newLng: lng,
                    oldLat: lastPos?.lat || currentPos.lat,
                    oldLng: lastPos?.lng || currentPos.lng,
                    positionChanged,
                    delayChanged,
                    status,
                    isDelayed: isDelayedFlag,
                    delayMinutes,
                    timestamp: Date.now()
                } 
            });

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

            // DYNAMIC NOTIFICATION: Show notification when delay is detected or changes
            const toast = document.getElementById('toast-notification');
            const toastMsgEl = document.getElementById('toast-msg');
            
            // Show notification if delayed (even if delayMsg is empty, generate one)
            if (delay) {
                const displayMsg = delayMsg || `Schedule ${scheduleId} is delayed by ${delayMinutes} minute${delayMinutes !== 1 ? 's' : ''}.`;
                // Clear existing timeout for this schedule
                if (scheduleNotificationTimeouts[scheduleId]) {
                    clearTimeout(scheduleNotificationTimeouts[scheduleId]);
                }
                
                // Show notification
                if (toast) {
                    // Force visibility
                    toast.style.display = 'block';
                    toast.style.visibility = 'visible';
                    toast.style.opacity = '1';
                    
                    if (toastMsgEl) {
                        toastMsgEl.textContent = displayMsg;
                    }
                    
                    // Verify toast is actually visible
                    const rect = toast.getBoundingClientRect();
                    const isVisible = toast.offsetWidth > 0 && toast.offsetHeight > 0 && 
                                     window.getComputedStyle(toast).display !== 'none';
                    
                    dbg({ 
                        h: 'H4', 
                        loc: `notification-shown-${scheduleId}`, 
                        msg: 'delay notification displayed', 
                        data: { 
                            scheduleId, 
                            delayMinutes, 
                            delayMsg: displayMsg,
                            delayChanged,
                            isNewDelay: !lastDelay || !lastDelay.isDelayed,
                            toastFound: !!toast,
                            toastDisplay: toast.style.display,
                            toastVisible: isVisible,
                            toastRect: { width: rect.width, height: rect.height, top: rect.top, left: rect.left },
                            computedDisplay: window.getComputedStyle(toast).display
                        } 
                    });
                    
                    // Auto-hide after 8 seconds (longer for important delays)
                    scheduleNotificationTimeouts[scheduleId] = setTimeout(() => {
                        toast.style.display = 'none';
                        delete scheduleNotificationTimeouts[scheduleId];
                    }, delayMinutes >= 10 ? 10000 : 8000);
                } else {
                    dbg({ 
                        h: 'H4', 
                        loc: `notification-failed-${scheduleId}`, 
                        msg: 'toast element not found', 
                        data: { scheduleId, delayMinutes, delayMsg: displayMsg } 
                    });
                }
            } else if (!delay && lastDelay && lastDelay.isDelayed) {
                // Delay cleared - show recovery notification
                if (toast) {
                    toast.style.display = 'block';
                    toast.style.visibility = 'visible';
                    toast.style.opacity = '1';
                    toast.style.background = 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)';
                    
                    if (toastMsgEl) {
                        toastMsgEl.textContent = `Schedule ${scheduleId}: Bus is back on schedule!`;
                    }
                    
                    setTimeout(() => {
                        toast.style.display = 'none';
                        // Reset background color
                        toast.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                    }, 5000);
                }
            }
        }

        function startSimulation(scheduleId) {
            const map = scheduleMaps[scheduleId];
            const marker = scheduleMarkers[scheduleId];
            if (!map || !marker) return;
            
            const path = schedulePaths[scheduleId] || schedulePaths[1];
            const statusBadge = document.getElementById(`status-badge-${scheduleId}`);
            const etaTime = document.getElementById(`eta-time-${scheduleId}`);
            const etas = { 1: [10, 8, 6, 4], 2: [15, 12, 9, 6], 3: [8, 6, 4, 2] };
            const etaValues = etas[scheduleId] || [10, 8, 6, 4];
            
            let idx = 0;
            const timer = setInterval(() => {
                if (idx >= path.length) {
                    clearInterval(timer);
                    if (statusBadge) {
                        statusBadge.textContent = 'Arrived';
                        statusBadge.style.background = '#e0f2fe';
                        statusBadge.style.color = '#0f172a';
                    }
                    if (etaTime) etaTime.textContent = '0 mins';
                    return;
                }
                const pos = path[idx];
                marker.setLatLng([pos.lat, pos.lng]);
                map.panTo([pos.lat, pos.lng]);
                if (etaTime) etaTime.textContent = `${etaValues[idx] || 0} mins`;
                idx++;
            }, 2000);
        }

        function triggerDelay(scheduleId) {
            const marker = scheduleMarkers[scheduleId];
            if (!marker) return;
            const pos = marker.getLatLng();
            // Simulate a 5-minute delay with explicit notification
            const delayMsg = `Traffic congestion detected on Schedule ${scheduleId}. Bus delayed by 5 minutes.`;
            updateMarker(scheduleId, pos.lat, pos.lng, 'delayed', 'Delayed', delayMsg, 'Delayed by 5 min', true, 5);
            
            dbg({ 
                h: 'H5', 
                loc: `triggerDelay-${scheduleId}`, 
                msg: 'manual delay triggered', 
                data: { scheduleId, delayMinutes: 5 } 
            });
        }
        
        // Test function to verify notification visibility
        function testNotification() {
            const toast = document.getElementById('toast-notification');
            const toastMsg = document.getElementById('toast-msg');
            if (toast && toastMsg) {
                toastMsg.textContent = 'Test: Delay alert notification is working!';
                toast.style.display = 'block';
                toast.style.visibility = 'visible';
                toast.style.opacity = '1';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 5000);
                dbg({ h: 'H5', loc: 'testNotification', msg: 'test notification shown', data: { toastFound: !!toast } });
            } else {
                dbg({ h: 'H5', loc: 'testNotification', msg: 'test notification failed', data: { toastFound: !!toast, toastMsgFound: !!toastMsg } });
            }
        }

        // Initialize all maps on page load
        document.addEventListener('DOMContentLoaded', () => {
            dbg({ h: 'H1', loc: 'DOMContentLoaded', msg: 'init start' });
            
            // Schedule 1: 7.00 AM - Bus ID 1
            initMap(1, [23.8103, 90.4125], 1);
            
            // Schedule 2: 8.30 AM - Bus ID 2 (simulated)
            initMap(2, [23.8150, 90.4100], 2);
            
            // Schedule 3: 12.00 PM - Bus ID 3 (simulated)
            initMap(3, [23.8050, 90.4000], 3);
        });
    </script>
    @endpush
</x-app-layout>