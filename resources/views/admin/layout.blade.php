<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - StudentMove</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
        /* Admin Top Navigation Bar */
        .admin-top-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .admin-top-nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 100%;
        }
        .admin-top-nav-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        .admin-top-nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .admin-top-nav-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.2s;
            font-weight: 500;
        }
        .admin-top-nav-link:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .admin-top-nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }
        .admin-top-nav-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .admin-top-nav-logout:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Admin Top Navigation Bar -->
    <nav class="admin-top-nav">
        <div class="admin-top-nav-content">
            <a href="{{ route('admin.dashboard') }}" class="admin-top-nav-brand">
                <i class="bi bi-shield-check"></i> Admin Panel
            </a>
            <div class="admin-top-nav-links">
                <a href="{{ route('admin.dashboard') }}" class="admin-top-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="admin-top-nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Users
                </a>
                <a href="{{ route('admin.buses.index') }}" class="admin-top-nav-link {{ request()->routeIs('admin.buses*') ? 'active' : '' }}">
                    <i class="bi bi-bus-front"></i> Buses
                </a>
                <div class="admin-top-nav-user">
                    <i class="bi bi-person-circle"></i> Admin
                    <form method="POST" action="{{ route('admin.logout') }}" style="display: inline; margin-left: 10px;">
                        @csrf
                        <button type="submit" class="admin-top-nav-logout" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 8px 15px; border-radius: 6px; cursor: pointer;">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        @include('admin.partials.navbar')

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
    
    <script>
        // Prevent back button access after logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was loaded from cache (back button)
                window.location.reload();
            }
        });
        
        // Clear any cached data
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                }
            });
        }
    </script>
</body>
</html>

