<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'StudentMove') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    @include('partials.pwa-head')
    @vite(['resources/css/app.css', 'resources/css/premium.css', 'resources/js/app.js'])
    @stack('styles')
    @php
        $firebaseConfig = [
            'apiKey' => config('services.firebase.api_key'),
            'authDomain' => config('services.firebase.auth_domain'),
            'projectId' => config('services.firebase.project_id'),
            'storageBucket' => config('services.firebase.storage_bucket'),
            'messagingSenderId' => config('services.firebase.messaging_sender_id'),
            'appId' => config('services.firebase.app_id'),
            'providers' => config('services.firebase.providers', ['google']),
            'syncUrl' => route('auth.firebase'),
        ];
    @endphp
    <script>
        window.__FIREBASE__ = @json($firebaseConfig);
    </script>
</head>
<body class="font-sans antialiased page-bg">
    <nav class="site-nav">
        <div class="nav-inner">
            <a href="{{ route('home') }}" class="nav-logo">StudentMove</a>
            <div class="nav-links">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('next-bus-arrival') }}" class="nav-link {{ request()->routeIs('next-bus-arrival') ? 'active' : '' }}">Live map</a>
            </div>
            <div class="nav-cta">
                <a href="{{ route('login') }}" class="nav-button">Sign in</a>
                <a href="{{ route('register') }}" class="nav-button ghost">Email signup</a>
                <a href="{{ route('driver.login') }}" class="nav-button ghost">Driver</a>
                <a href="{{ route('admin.login') }}" class="nav-button ghost">Admin</a>
            </div>
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>
    @include('partials.pwa-install')
    @vite(['resources/js/firebase-auth.js'])
    @stack('scripts')
</body>
</html>
