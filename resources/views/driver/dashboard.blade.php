<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .gps-indicator {
            font-size: 0.85rem;
            color: #14a39c;
            text-align: center;
            margin-top: 1.5rem;
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

        <div class="gps-indicator">
            <i class="bi bi-broadcast blink"></i> GPS transmitting<br>
            <span id="coords">Lat: -- | Lng: --</span>
        </div>

        <form method="POST" action="{{ route('driver.logout') }}" class="mt-4">
            @csrf
            <button class="btn w-100" style="border:1px solid rgba(255,255,255,0.25);color:#f3f5f7;">End shift</button>
        </form>
    </div>

    <script>
        let lat = 23.8103;
        let lng = 90.4125;

        function sendGpsPing() {
            lat += (Math.random() - 0.5) * 0.001;
            lng += (Math.random() - 0.5) * 0.001;
            document.getElementById('coords').innerText = `Lat: ${lat.toFixed(4)} | Lng: ${lng.toFixed(4)}`;
            fetch("{{ route('driver.gps') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ lat: lat, lng: lng })
            }).catch(() => {});
        }

        setInterval(sendGpsPing, 5000);
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) window.location.reload();
        });
    </script>
</body>
</html>
