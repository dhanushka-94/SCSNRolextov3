<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') · {{ config('app.short_name') }}</title>
    <link rel="icon" href="{{ asset('SCSNR-logo.png') }}">
    <x-app-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-leaf/10 blur-3xl"></div>
        <div class="absolute -bottom-20 -right-16 h-80 w-80 rounded-full bg-tan/30 blur-3xl"></div>
    </div>
    @yield('content')
    @stack('scripts')
</body>
</html>
