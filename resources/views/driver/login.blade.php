<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login · StudentMove</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    @vite(['resources/css/premium.css', 'resources/js/app.js'])
    <style>
        .driver-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,0.96);
            border: 1px solid rgba(18,22,28,0.08);
            border-radius: 0.85rem;
            padding: 2rem;
            box-shadow: 0 18px 50px rgba(18,22,28,0.1);
        }
        .driver-card h1 {
            font-family: Syne, sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 0.35rem;
            color: #12161c;
        }
        .driver-card .lede { color: #5b6572; margin-bottom: 1.5rem; }
        .driver-submit {
            width: 100%;
            background: #e0952c;
            color: #12161c;
            border: none;
            font-weight: 600;
            padding: 0.85rem;
            border-radius: 0.5rem;
        }
        .driver-submit:hover { background: #ebb04a; }
    </style>
</head>
<body class="page-bg">
    <nav class="site-nav">
        <div class="nav-inner">
            <a href="{{ route('home') }}" class="nav-logo">StudentMove</a>
            <div class="nav-cta">
                <a href="{{ route('home') }}" class="nav-button ghost">Home</a>
                <a href="{{ route('admin.login') }}" class="nav-button ghost">Admin</a>
            </div>
        </div>
    </nav>

    <main class="d-flex align-items-center justify-content-center" style="min-height:calc(100vh - 72px);padding:2rem 1rem;">
        <div class="driver-card sm-reveal">
            <p class="sm-eyebrow">Driver</p>
            <h1>Start shift</h1>
            <p class="lede">Select your bus and enter the driver PIN.</p>

            @if(session('error'))
                <div class="sm-flash sm-flash--err" style="margin-bottom:1rem;">{{ session('error') }}</div>
            @endif

            @php $buses = \App\Models\BusSchedule::all(); @endphp
            @if($buses->count() === 0)
                <div class="sm-flash sm-flash--err" style="margin-bottom:1rem;">
                    No buses yet. Create them in the <a href="{{ route('admin.login') }}">admin panel</a> first.
                </div>
            @endif

            <form method="POST" action="{{ route('driver.login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Bus</label>
                    <select name="bus_id" class="form-select" required>
                        <option value="">Select a bus</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}">{{ $bus->bus_number ?? 'Bus #'.$bus->id }} — {{ $bus->route_name ?? 'Route' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Driver PIN</label>
                    <input type="password" name="password" class="form-control" placeholder="PIN" required>
                </div>
                <button type="submit" class="driver-submit">Start shift</button>
            </form>
        </div>
    </main>
</body>
</html>
