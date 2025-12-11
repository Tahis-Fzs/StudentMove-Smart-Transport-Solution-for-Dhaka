@props(['title' => config('app.name', 'Laravel')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="/css/base.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased page-bg">
    <div class="min-h-screen">
        <nav class="site-nav">
            <div class="nav-inner">
                <a href="{{ route('home') }}" class="nav-logo">StudentMove</a>
                <div class="nav-links">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('subscription') }}" class="nav-link {{ request()->routeIs('subscription') ? 'active' : '' }}">Subscription</a>
                    <a href="{{ route('next-bus-arrival') }}" class="nav-link {{ request()->routeIs('next-bus-arrival') ? 'active' : '' }}">Live Location</a>
                    <a href="{{ route('route-suggestion') }}" class="nav-link {{ request()->routeIs('route-suggestion') ? 'active' : '' }}">Personalized Route</a>
                    @auth
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">Profile</a>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    @endauth
                </div>
                <div class="nav-cta">
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-button">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="nav-button ghost">Sign in</a>
                    @endauth
                </div>
            </div>
        </nav>

        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        @if(session('success'))
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 16px 20px; margin: 16px auto; max-width: 1200px; border-radius: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); display: flex; align-items: center; gap: 12px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <div style="flex: 1;">{!! session('success') !!}</div>
            </div>
        @endif

        @if(session('error'))
            <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 16px 20px; margin: 16px auto; max-width: 1200px; border-radius: 12px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); display: flex; align-items: center; gap: 12px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div style="flex: 1;">{!! session('error') !!}</div>
            </div>
        @endif

        <main>
            {{ $slot }}
        </main>
    </div>
    @stack('scripts')
</body>
</html>

