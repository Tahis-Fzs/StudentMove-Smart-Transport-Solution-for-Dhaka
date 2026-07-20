@props(['title' => 'StudentMove'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/css/premium.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased page-bg">
    <div class="min-h-screen">
        <nav class="site-nav">
            <div class="nav-inner">
                <a href="{{ route('home') }}" class="nav-logo">StudentMove</a>
                <div class="nav-links">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('next-bus-arrival') }}" class="nav-link {{ request()->routeIs('next-bus-arrival') ? 'active' : '' }}">Live map</a>
                    @auth
                        <a href="{{ route('route-suggestion') }}" class="nav-link {{ request()->routeIs('route-suggestion') ? 'active' : '' }}">Routes</a>
                        <a href="{{ route('subscription') }}" class="nav-link {{ request()->routeIs('subscription*') ? 'active' : '' }}">Plans</a>
                        <a href="{{ route('notifications') }}" class="nav-link {{ request()->routeIs('notifications') ? 'active' : '' }}">Alerts</a>
                        <a href="{{ route('feedback.index') }}" class="nav-link {{ request()->routeIs('feedback.*') ? 'active' : '' }}">Feedback</a>
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">Profile</a>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    @endauth
                </div>
                <div class="nav-cta">
                    @auth
                        <form method="POST" action="{{ route('logout') }}" style="display: inline; margin: 0;">
                            @csrf
                            <button type="submit" class="nav-button">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="nav-button">Sign in</a>
                        <a href="{{ route('register') }}" class="nav-button ghost">Register</a>
                        <a href="{{ route('driver.login') }}" class="nav-button ghost">Driver</a>
                        <a href="{{ route('admin.login') }}" class="nav-button ghost">Admin</a>
                    @endauth
                </div>
            </div>
        </nav>

        @if (isset($header))
            <header style="max-width:1280px;margin:1rem auto 0;padding:0 1.25rem;">
                {{ $header }}
            </header>
        @endif

        @if(session('success'))
            <div class="sm-flash sm-flash--ok" style="max-width:1280px;padding-left:1.25rem;padding-right:1.25rem;">
                <div style="flex:1;">{!! session('success') !!}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="sm-flash sm-flash--err" style="max-width:1280px;padding-left:1.25rem;padding-right:1.25rem;">
                <div style="flex:1;">{!! session('error') !!}</div>
            </div>
        @endif

        <main style="width: 100%; overflow-x: auto;">
            {{ $slot }}
        </main>
    </div>
    @stack('scripts')
</body>
</html>
