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
    @vite(['resources/css/premium.css', 'resources/js/app.js'])
    <style>
        body.admin-body { font-family: 'IBM Plex Sans', sans-serif; }
        .sidebar { min-height: 100vh; background: #1e2630; color: white; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 10px 15px; display: block; border-radius: 0.35rem; margin: 0.15rem 0.5rem; }
        .sidebar a:hover, .sidebar a.active { background: rgba(20, 163, 156, 0.22); color: white; }
        .admin-container { padding: 20px; overflow-x: auto; }
        .admin-header { margin-bottom: 30px; }
        .admin-header h1 { color: #12161c; margin-bottom: 10px; font-family: Syne, sans-serif; letter-spacing: -0.03em; font-weight: 800; }
        .admin-header p { color: #5b6572; }
        .admin-toolbar { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .action-btn { background: #e0952c; color: #12161c; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; }
        .action-btn:hover { background: #ebb04a; color: #12161c; }
        .btn-back { background: #5b6572; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; }
        .btn-back:hover { background: #3a4552; color: white; }
        .admin-section { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 18px 50px rgba(18,22,28,0.08); border: 1px solid rgba(18,22,28,0.06); }
        .users-table table { width: 100%; border-collapse: collapse; }
        .users-table th, .users-table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; word-wrap: break-word; overflow-wrap: break-word; }
        .users-table th { background: #f7f8fa; font-weight: 600; color: #3d4654; }
        * { box-sizing: border-box; }
        .section-header { margin-bottom: 20px; }
        .section-header h2 { color: #12161c; font-size: 1.5rem; font-family: Syne, sans-serif; font-weight: 700; letter-spacing: -0.03em; }
        .admin-top-nav {
            background: #12161c;
            color: white;
            padding: 15px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(12px);
        }
        .admin-top-nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 100%;
        }
        .admin-top-nav-brand {
            font-size: 1.35rem;
            font-weight: 800;
            font-family: Syne, sans-serif;
            letter-spacing: -0.03em;
            color: white;
            text-decoration: none;
        }
        .admin-top-nav-links {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        .admin-top-nav-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.2s;
            font-weight: 500;
        }
        .admin-top-nav-link:hover,
        .admin-top-nav-link.active {
            background: rgba(255,255,255,0.12);
            color: white;
        }
        .admin-top-nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }
        .admin-top-nav-logout {
            background: #e0952c;
            color: #12161c;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        .admin-top-nav-logout:hover {
            background: #ebb04a;
            color: #12161c;
        }
    </style>
</head>
<body class="admin-body">
    <!-- Admin Top Navigation Bar -->
    <nav class="admin-top-nav">
        <div class="admin-top-nav-content">
            <a href="{{ route('admin.dashboard') }}" class="admin-top-nav-brand">
                StudentMove Admin
            </a>
            <div class="admin-top-nav-links">
                <a href="{{ route('admin.dashboard') }}" class="admin-top-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="admin-top-nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    Users
                </a>
                <a href="{{ route('admin.buses.index') }}" class="admin-top-nav-link {{ request()->routeIs('admin.buses*') ? 'active' : '' }}">
                    Buses
                </a>
                <a href="{{ route('home') }}" class="admin-top-nav-link">Site</a>
                <div class="admin-top-nav-user">
                    <span>Admin</span>
                    <form method="POST" action="{{ route('admin.logout') }}" style="display: inline; margin-left: 10px;">
                        @csrf
                        <button type="submit" class="admin-top-nav-logout">
                            Logout
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

