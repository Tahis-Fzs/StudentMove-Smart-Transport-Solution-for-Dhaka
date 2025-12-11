<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="/css/base.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans text-gray-900 antialiased page-bg">
    <nav class="site-nav">
        <div class="nav-inner">
            <a href="{{ route('home') }}" class="nav-logo">StudentMove</a>
            <div class="nav-links">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('subscription') }}" class="nav-link {{ request()->routeIs('subscription') ? 'active' : '' }}">Subscription</a>
                <a href="{{ route('next-bus-arrival') }}" class="nav-link {{ request()->routeIs('next-bus-arrival') ? 'active' : '' }}">Live Location</a>
                <a href="{{ route('route-suggestion') }}" class="nav-link {{ request()->routeIs('route-suggestion') ? 'active' : '' }}">Personalized Route</a>
            </div>
            <div class="nav-cta">
                <a href="{{ route('login') }}" class="nav-button">Sign in</a>
                <a href="{{ route('register') }}" class="nav-button ghost">Register</a>
                <a href="{{ route('driver.login') }}" class="nav-button ghost" style="margin-left: 8px; font-size: 0.875rem;">🚌 Driver</a>
                <a href="{{ route('admin.login') }}" class="nav-button ghost" style="margin-left: 8px; font-size: 0.875rem;">⚙️ Admin</a>
            </div>
        </div>
    </nav>

    {{ $slot }}
    @stack('scripts')
</body>
</html>

