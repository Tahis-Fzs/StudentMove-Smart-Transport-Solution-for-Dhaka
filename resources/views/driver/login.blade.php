HTML
<!DOCTYPE html><html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head><body class="bg-dark d-flex align-items-center justify-content-center" style="height: 100vh;">
    
    <div class="card p-4" style="width: 100%; max-width: 350px;">
        <h3 class="text-center mb-3">🚌 Driver App</h3>
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('driver.login.post') }}">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Select Your Bus</label>
                <select name="bus_id" class="form-select" required>
                    @foreach(\App\Models\BusSchedule::all() as $bus)
                        <option value="{{ $bus->id }}">{{ $bus->bus_number }} ({{ $bus->route_name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Driver PIN</label>
                <input type="password" name="password" class="form-control" placeholder="Enter PIN (driver123)" required>
            </div>

            <button type="submit" class="btn btn-warning w-100">Start Shift</button>
        </form>
    </div>
</body></html>