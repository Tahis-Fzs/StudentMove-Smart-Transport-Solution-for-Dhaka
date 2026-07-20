<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Driver · {{ $bus->bus_number }} · StudentMove</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    @vite(['resources/css/premium.css', 'resources/js/app.js'])
    <style>
        body.driver-live {
            background: #12161c !important;
            color: #f3f5f7;
            min-height: 100vh;
            font-family: 'IBM Plex Sans', sans-serif;
        }
        .app-header {
            background: #e0952c;
            color: #12161c;
            padding: 1.25rem 1rem 1.5rem;
            border-radius: 0 0 1.25rem 1.25rem;
            margin-bottom: 1.25rem;
            text-align: center;
        }
        .app-header h3 {
            font-family: Syne, sans-serif;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0;
        }
        .status-btn {
            width: 100%;
            padding: 0.95rem;
            margin-bottom: 0.65rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .gps-panel {
            margin-top: 1.5rem;
            padding: 1rem 1.1rem;
            border-radius: 0.75rem;
            background: #1e2630;
            border: 1px solid rgba(255,255,255,0.08);
            text-align: center;
        }
        .gps-panel__label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #9aa3ad;
            margin-bottom: 0.45rem;
        }
        .gps-panel__state {
            font-family: Syne, sans-serif;
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 0.35rem;
        }
        .gps-panel__state.is-live { color: #14a39c; }
        .gps-panel__state.is-wait { color: #e0952c; }
        .gps-panel__state.is-err { color: #c45c4a; }
        .gps-panel__meta {
            font-size: 0.82rem;
            color: #9aa3ad;
            line-height: 1.45;
        }
        .blink { animation: blinker 1.5s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.35; } }
        .status-panel {
            background: #1e2630;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 0.75rem;
            padding: 1.25rem;
            text-align: center;
            margin-bottom: 1.25rem;
        }
        .gps-demo-btn {
            margin-top: 0.75rem;
            width: 100%;
            padding: 0.65rem;
            border-radius: 0.45rem;
            border: 1px solid rgba(255,255,255,0.18);
            background: transparent;
            color: #f3f5f7;
            font-size: 0.85rem;
            display: none;
        }
    </style>
</head>
<body class="driver-live">
    <div class="app-header sm-reveal">
        <h3>Bus {{ $bus->bus_number }}</h3>
        <p class="mb-0" style="opacity:0.8;">{{ $bus->route_name }}</p>
    </div>

    <div class="container sm-reveal" style="max-width: 400px;">
        <div class="status-panel">
            <div style="color:#9aa3ad;font-size:0.85rem;margin-bottom:0.35rem;">Current status</div>
            <h2 class="text-uppercase mb-0" style="font-family:Syne,sans-serif;color: {{ $bus->status == 'delayed' ? '#c45c4a' : '#14a39c' }};">
                {{ $bus->status }}
            </h2>
        </div>

        <h6 style="color:#9aa3ad;">Update status</h6>
        <form method="POST" action="{{ route('driver.status') }}">
            @csrf
            <button name="status" value="on_time" class="status-btn btn-success">Mark on time</button>
            <button name="status" value="delayed" class="status-btn btn-danger">Report delay</button>
            <button name="status" value="stopped" class="status-btn btn-secondary">Bus stopped</button>
        </form>

        <div class="gps-panel">
            <div class="gps-panel__label">Phone GPS</div>
            <div id="gps-state" class="gps-panel__state is-wait">Requesting…</div>
            <div id="gps-meta" class="gps-panel__meta">
                Allow location when prompted.<br>
                HTTPS required on a real phone.
            </div>
            <button type="button" id="gps-demo-btn" class="gps-demo-btn">Use demo GPS instead</button>
        </div>

        <form method="POST" action="{{ route('driver.logout') }}" class="mt-4">
            @csrf
            <button class="btn w-100" style="border:1px solid rgba(255,255,255,0.25);color:#f3f5f7;">End shift</button>
        </form>
    </div>

    <script>
        (function () {
            const stateEl = document.getElementById('gps-state');
            const metaEl = document.getElementById('gps-meta');
            const demoBtn = document.getElementById('gps-demo-btn');
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const pingUrl = @json(route('driver.gps'));

            let watchId = null;
            let demoTimer = null;
            let lastSentAt = 0;
            let lastLat = null;
            let lastLng = null;
            let demoLat = {{ $bus->current_lat ? (float) $bus->current_lat : 23.8103 }};
            let demoLng = {{ $bus->current_lng ? (float) $bus->current_lng : 90.4125 }};
            let demoHeading = 45;

            function setState(kind, title, detail) {
                stateEl.className = 'gps-panel__state is-' + kind;
                stateEl.textContent = title;
                metaEl.innerHTML = detail;
            }

            function bearing(lat1, lng1, lat2, lng2) {
                const toRad = Math.PI / 180;
                const φ1 = lat1 * toRad, φ2 = lat2 * toRad;
                const Δλ = (lng2 - lng1) * toRad;
                const y = Math.sin(Δλ) * Math.cos(φ2);
                const x = Math.cos(φ1) * Math.sin(φ2) - Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
                return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
            }

            function postLocation(lat, lng, accuracy, heading, speedMs) {
                const now = Date.now();
                if (now - lastSentAt < 1000) return;
                lastSentAt = now;

                let h = (heading != null && !Number.isNaN(heading)) ? heading : null;
                if (h == null && lastLat != null && lastLng != null) {
                    const moved = Math.abs(lastLat - lat) > 0.00001 || Math.abs(lastLng - lng) > 0.00001;
                    if (moved) h = bearing(lastLat, lastLng, lat, lng);
                }
                lastLat = lat;
                lastLng = lng;

                const payload = {
                    lat: lat,
                    lng: lng,
                    accuracy: accuracy ?? null,
                    heading: h,
                    speed: (speedMs != null && speedMs >= 0) ? speedMs : null,
                };

                fetch(pingUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(payload),
                    keepalive: true,
                }).catch(function () {});
            }

            function onPosition(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const acc = pos.coords.accuracy;
                const heading = (pos.coords.heading != null && !Number.isNaN(pos.coords.heading))
                    ? pos.coords.heading : null;
                const speed = (pos.coords.speed != null && pos.coords.speed >= 0)
                    ? pos.coords.speed : null;
                const speedKmh = speed != null ? (speed * 3.6) : null;

                let detail = 'Lat ' + lat.toFixed(5) + ' · Lng ' + lng.toFixed(5) +
                    '<br>Accuracy ±' + Math.round(acc) + ' m';
                if (heading != null) detail += ' · Heading ' + Math.round(heading) + '°';
                if (speedKmh != null) detail += ' · ' + speedKmh.toFixed(1) + ' km/h';
                detail += '<br>sending to students';

                setState('live', 'Live', detail);
                postLocation(lat, lng, acc, heading, speed);
            }

            function onError(err) {
                demoBtn.style.display = 'block';
                let msg = 'Location unavailable.';
                if (err && err.code === 1) msg = 'Permission denied — enable location for this site.';
                if (err && err.code === 2) msg = 'Position unavailable — try outdoors / turn on GPS.';
                if (err && err.code === 3) msg = 'Timed out waiting for GPS.';
                if (!window.isSecureContext) {
                    msg = 'GPS needs HTTPS (or localhost). Open this page via your Cloudflare tunnel.';
                }
                setState('err', 'GPS blocked', msg + '<br>You can still run a demo ping for testing.');
            }

            function startRealGps() {
                if (!navigator.geolocation) {
                    onError({ code: 2 });
                    return;
                }
                if (!window.isSecureContext) {
                    onError({ code: 2 });
                    return;
                }

                setState('wait', 'Requesting…', 'Waiting for phone GPS fix…');

                watchId = navigator.geolocation.watchPosition(onPosition, onError, {
                    enableHighAccuracy: true,
                    maximumAge: 1000,
                    timeout: 15000,
                });
            }

            function startDemoGps() {
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
                if (demoTimer) clearInterval(demoTimer);

                setState('wait', 'Demo mode', 'Simulated walk near Dhaka — not your real phone.');
                demoTimer = setInterval(function () {
                    const stepLat = Math.cos(demoHeading * Math.PI / 180) * 0.00018;
                    const stepLng = Math.sin(demoHeading * Math.PI / 180) * 0.00018;
                    demoHeading = (demoHeading + (Math.random() - 0.5) * 25 + 360) % 360;
                    demoLat += stepLat;
                    demoLng += stepLng;
                    const speedMs = 8 + Math.random() * 4; // ~30–40 km/h
                    setState(
                        'live',
                        'Demo live',
                        'Lat ' + demoLat.toFixed(5) + ' · Lng ' + demoLng.toFixed(5) +
                        '<br>Heading ' + Math.round(demoHeading) + '° · ' + (speedMs * 3.6).toFixed(0) + ' km/h (fake)'
                    );
                    postLocation(demoLat, demoLng, 25, demoHeading, speedMs);
                }, 1000);
            }

            demoBtn.addEventListener('click', startDemoGps);
            startRealGps();

            window.addEventListener('pageshow', function (event) {
                if (event.persisted) window.location.reload();
            });
        })();
    </script>
</body>
</html>
