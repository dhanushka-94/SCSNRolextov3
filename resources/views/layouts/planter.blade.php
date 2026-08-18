<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Planter Portal') · {{ config('app.short_name') }}</title>
    <link rel="icon" href="{{ asset('SCSNR-logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-cream">
    <header class="sticky top-0 z-20 shrink-0 border-b border-sand bg-paper/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('SCSNR-logo.png') }}" alt="{{ config('app.short_name') }}" class="h-11 w-11 rounded-full bg-white object-contain p-1 shadow-sm">
                <div>
                    <p class="text-sm font-semibold text-forest-dark">{{ config('app.short_name') }} Planter Portal</p>
                    <p class="text-xs text-muted">@yield('heading', 'Rubber Planter Registration')</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('planter.dashboard') }}" class="btn-secondary px-3 py-2 {{ request()->routeIs('planter.dashboard') ? 'bg-sand' : '' }}">Dashboard</a>
                <a href="{{ route('planter.profile') }}" class="btn-secondary px-3 py-2 {{ request()->routeIs('planter.profile*') ? 'bg-sand' : '' }}">Profile</a>
                <form method="POST" action="{{ route('planter.logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary px-3 py-2">Sign out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-6 sm:px-6">
        @if (session('success'))
            <div id="flash-banner" class="mb-5 rounded-2xl border border-leaf/20 bg-leaf/10 px-4 py-3 text-sm font-medium text-forest transition">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>

    <x-app-footer class="app-footer-sticky mx-auto w-full max-w-5xl px-4 py-4 sm:px-6" />
</body>
</html>
