HTML
<!DOCTYPE html><html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Driver Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .top-nav {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000;
            padding: 15px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .top-nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .top-nav-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: #000;
            text-decoration: none;
        }
        .top-nav-link {
            color: #000;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.2s;
            font-weight: 500;
        }
        .top-nav-link:hover {
            background: rgba(0,0,0,0.1);
            color: #000;
        }
    </style>
</head>
<body class="bg-dark" style="min-height: 100vh;">
    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav-content">
            <a href="{{ route('home') }}" class="top-nav-brand">
                <i class="bi bi-bus-front"></i> StudentMove
            </a>
            <div>
                <a href="{{ route('home') }}" class="top-nav-link">
                    <i class="bi bi-house"></i> Home
                </a>
                <a href="{{ route('admin.login') }}" class="top-nav-link">
                    <i class="bi bi-gear"></i> Admin
                </a>
            </div>
        </div>
    </nav>
    
    <div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 70px); padding: 20px;">
    <div class="card p-4" style="width: 100%; max-width: 350px;">
        <h3 class="text-center mb-3">🚌 Driver App</h3>
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @php
            $buses = \App\Models\BusSchedule::all();
        @endphp
        @if($buses->count() === 0)
            <div class="alert alert-warning">
                <strong>⚠️ No Buses Available</strong><br>
                Please create buses from the <a href="{{ route('admin.login') }}" target="_blank">Admin Panel</a> first.
            </div>
        @endif

        <form method="POST" action="{{ route('driver.login.post') }}">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Select Your Bus</label>
                <select name="bus_id" class="form-select" required>
                    <option value="">-- Select a Bus --</option>
                    @php
                        $buses = \App\Models\BusSchedule::all();
                    @endphp
                    @if($buses->count() > 0)
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}">{{ $bus->bus_number ?? 'Bus #' . $bus->id }} - {{ $bus->route_name ?? 'Route ' . $bus->id }}</option>
                        @endforeach
                    @else
                        <option value="" disabled>No buses available. Please add buses from admin panel.</option>
                    @endif
                </select>
                @if($buses->count() === 0)
                    <small class="text-danger">No buses found in database. Please create buses from the admin panel first.</small>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label">Driver PIN</label>
                <input type="password" name="password" class="form-control" placeholder="Enter PIN (driver123)" required>
            </div>

            <button type="submit" class="btn btn-warning w-100">Start Shift</button>
        </form>
    </div>
    </div>
    
    <script>
        // Prevent back button access after logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was loaded from cache (back button)
                window.location.reload();
            }
        });
        
        // Clear form on page load if coming from back button
        if (performance.navigation.type === 2) {
            document.querySelector('input[name="password"]').value = '';
            document.querySelector('select[name="bus_id"]').value = '';
        }
    </script>
</body></html>