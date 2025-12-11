<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - StudentMove</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #343a40; color: white; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #495057; color: white; }
        .admin-container { padding: 20px; }
        .admin-header { margin-bottom: 30px; }
        .admin-header h1 { color: #333; margin-bottom: 10px; }
        .admin-header p { color: #666; }
        .admin-toolbar { margin-bottom: 20px; display: flex; gap: 10px; }
        .action-btn { background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
        .action-btn:hover { background: #0056b3; color: white; }
        .btn-back { background: #6c757d; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
        .btn-back:hover { background: #5a6268; color: white; }
        .admin-section { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .users-table table { width: 100%; border-collapse: collapse; }
        .users-table th, .users-table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; word-wrap: break-word; overflow-wrap: break-word; }
        .users-table th { background: #f8f9fa; font-weight: 600; }
        .admin-container { overflow-x: auto; }
        * { box-sizing: border-box; }
        .section-header { margin-bottom: 20px; }
        .section-header h2 { color: #333; font-size: 1.5rem; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3" style="width: 250px;">
            <h4 class="mb-4 text-center">Admin Panel</h4>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a href="{{ route('admin.offers.index') }}" class="{{ request()->routeIs('admin.offers*') ? 'active' : '' }}">
                <i class="bi bi-tag"></i> Offers
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                <i class="bi bi-bell"></i> Notifications
            </a>
            <a href="{{ route('admin.buses.index') }}" class="{{ request()->routeIs('admin.buses*') || request()->routeIs('admin.bus*') ? 'active' : '' }}">
                <i class="bi bi-bus-front"></i> Buses
            </a>
            <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Reports
            </a>
            <a href="{{ route('admin.logs') }}" class="{{ request()->routeIs('admin.logs') ? 'active' : '' }}">
                <i class="bi bi-file-text"></i> Activity Logs
            </a>
            
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-5">
                @csrf
                <button type="submit" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>

        <div class="flex-grow-1">
            @if(session('success'))
                <div class="alert alert-success m-3" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger m-3" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>

