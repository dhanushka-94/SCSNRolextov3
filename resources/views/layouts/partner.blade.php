<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.short_name') }} Partner Portal · {{ config('app.full_name') }}</title>
    <link rel="icon" href="{{ asset('SCSNR-logo.png') }}">
    <x-app-fonts :display="true" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-ink antialiased">
    @yield('content')
</body>
</html>
