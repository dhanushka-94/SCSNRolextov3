<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('SCSNR-logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-cream">
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-bark-dark/40 lg:hidden"></div>

    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-forest-dark text-white transition-transform duration-200 lg:translate-x-0">
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
            <img src="{{ asset('SCSNR-logo.png') }}" alt="{{ config('app.short_name') }}" class="h-12 w-12 rounded-full bg-white object-contain p-1 shadow-sm">
            <div class="min-w-0">
                <p class="text-sm font-semibold tracking-wide">{{ config('app.short_name') }}</p>
                <p class="text-[11px] leading-4 text-sand/80">{{ config('app.full_name') }}</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-tan">Workspace</p>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 4l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.planters.approval-lobby') }}" class="nav-link {{ request()->routeIs('admin.planters.approval-lobby') ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0"/></svg>
                Approval lobby
                @php($pendingPlanterCount = \App\Models\Planter::query()->where('status', \App\Models\Planter::STATUS_PENDING)->count())
                @if ($pendingPlanterCount > 0)
                    <span class="ml-auto rounded-full bg-tan px-2 py-0.5 text-[11px] font-bold text-forest-dark">{{ $pendingPlanterCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.planters.index') }}" class="nav-link {{ request()->routeIs('admin.planters.*') && ! request()->routeIs('admin.planters.approval-lobby') ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M5 21V7.5L12 3l7 4.5V21M9 21v-6h6v6"/></svg>
                Rubber planters
            </a>
            @if (auth('web')->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8m13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    System users
                </a>
            @endif
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-leaf text-sm font-semibold">
                    {{ strtoupper(substr(auth('web')->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">{{ auth('web')->user()->name }}</p>
                    <p class="truncate text-xs text-sand/80">{{ \App\Models\User::roles()[auth('web')->user()->role] }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn-secondary w-full border-white/15 bg-white/5 text-white hover:bg-white/10">Sign out</button>
            </form>
            <p class="mt-3 text-center text-[10px] leading-4 text-sand/60">
                {{ config('app.short_name') }} v{{ config('app.version') }}
            </p>
        </div>
    </aside>

    <div class="flex min-h-0 flex-1 flex-col lg:pl-72">
        <header class="sticky top-0 z-20 border-b border-sand bg-paper/90 backdrop-blur">
            <div class="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" data-sidebar-toggle class="rounded-xl border border-line p-2 text-bark lg:hidden" aria-label="Open menu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-semibold text-forest-dark">@yield('heading')</h1>
                        <p class="text-xs text-muted">@yield('subheading', config('app.full_name'))</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @yield('actions')
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div id="flash-banner" class="mb-5 rounded-2xl border border-leaf/20 bg-leaf/10 px-4 py-3 text-sm font-medium text-forest transition">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('user'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    {{ $errors->first('user') }}
                </div>
            @endif

            @yield('content')
        </main>

        <x-app-footer class="app-footer-sticky px-4 py-4 sm:px-6 lg:px-8" />
    </div>
</body>
</html>
