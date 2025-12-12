HTML
<!DOCTYPE html><html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Driver App - Active</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #212529; color: white; padding-bottom: 50px; }
        .app-header { background: #ffc107; color: black; padding: 15px; border-radius: 0 0 20px 20px; margin-bottom: 20px; }
        .status-btn { width: 100%; padding: 15px; margin-bottom: 10px; border: none; border-radius: 10px; font-weight: bold; }
        .gps-indicator { font-size: 0.8rem; color: #00ff00; text-align: center; margin-top: 20px; }
        .blink { animation: blinker 1.5s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
    </style></head><body>

    <div class="app-header text-center">
        <h3>🚌 Bus {{ $bus->bus_number }}</h3>
        <p class="mb-0">{{ $bus->route_name }}</p>
    </div>

    <div class="container" style="max-width: 400px;">
        
        <div class="card bg-dark border-secondary mb-4">
            <div class="card-body text-center text-white">
                <h5 class="text-muted">Current Status</h5>
                <h2 class="text-uppercase" style="color: {{ $bus->status == 'delayed' ? 'red' : '#00ff00' }}">
                    {{ $bus->status }}
                </h2>
            </div>
        </div>

        <h6 class="text-muted ms-1">Update Status</h6>
        
        <form method="POST" action="{{ route('driver.status') }}">
            @csrf
            <button name="status" value="on_time" class="status-btn btn-success">
                <i class="bi bi-check-circle-fill"></i> Mark On Time
            </button>
            <button name="status" value="delayed" class="status-btn btn-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> Report Traffic / Delay
            </button>
            <button name="status" value="stopped" class="status-btn btn-secondary">
                <i class="bi bi-stop-circle-fill"></i> Bus Stopped
            </button>
        </form>

        <div class="gps-indicator">
            <i class="bi bi-broadcast blink"></i> GPS Transmitting...<br>
            <span id="coords">Lat: -- | Lng: --</span>
        </div>

        <form method="POST" action="{{ route('driver.logout') }}" class="mt-4">
            @csrf
            <button class="btn btn-outline-light w-100">End Shift (Logout)</button>
        </form>
    </div>

    <script>
        // Simulate GPS movement starting from Dhaka
        let lat = 23.8103;
        let lng = 90.4125;

        function sendGpsPing() {
            // 1. Simulate movement (add tiny random value)
            lat += (Math.random() - 0.5) * 0.001; 
            lng += (Math.random() - 0.5) * 0.001;

            // 2. Update UI
            document.getElementById('coords').innerText = `Lat: ${lat.toFixed(4)} | Lng: ${lng.toFixed(4)}`;

            // 3. Send to Server (AJAX)
            fetch("{{ route('driver.gps') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ lat: lat, lng: lng })
            })
            .then(res => res.json())
            .then(data => console.log('GPS Sent:', data.message))
            .catch(err => console.error('GPS Error:', err));
        }

        // Run every 5 seconds
        setInterval(sendGpsPing, 5000);
        
        // Prevent back button access after logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was loaded from cache (back button)
                window.location.reload();
            }
        });
    </script></body></html>