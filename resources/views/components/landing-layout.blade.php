<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="StudentMove — smart transport for Dhaka city students. Live buses, intelligent routes, student subscriptions.">
    <title>{{ $title ?? 'StudentMove' }}</title>
    @vite(['resources/css/landing.css', 'resources/css/premium.css', 'resources/js/landing.js'])
</head>
<body class="landing-body">
    {{ $slot }}
</body>
</html>
