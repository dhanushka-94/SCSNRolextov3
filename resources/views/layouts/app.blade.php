<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('SCSNR-logo.png') }}">
    <x-app-fonts />
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
            <a href="{{ route('admin.planters.registration-lobby') }}" class="nav-link {{ request()->routeIs('admin.planters.registration-lobby') || (request()->routeIs(['admin.planters.show', 'admin.planters.edit']) && optional(request()->route('planter'))->isPending()) ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0"/></svg>
                Registration Lobby
                @php($pendingPlanterCount = \App\Models\Planter::query()->where('status', \App\Models\Planter::STATUS_PENDING)->count())
                @if ($pendingPlanterCount > 0)
                    <span class="ml-auto rounded-full bg-tan px-2 py-0.5 text-[11px] font-bold text-forest-dark">{{ $pendingPlanterCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.planters.index') }}" class="nav-link {{ request()->routeIs(['admin.planters.index', 'admin.planters.create']) || (request()->routeIs(['admin.planters.show', 'admin.planters.edit']) && optional(request()->route('planter'))->isApproved()) ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M5 21V7.5L12 3l7 4.5V21M9 21v-6h6v6"/></svg>
                Registry
            </a>
            <a href="{{ route('admin.planters.rejected') }}" class="nav-link {{ request()->routeIs('admin.planters.rejected') || (request()->routeIs(['admin.planters.show', 'admin.planters.edit']) && optional(request()->route('planter'))->isRejected()) ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                Rejected
            </a>

            <p class="px-3 pb-2 pt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-tan">Audits</p>
            <a href="{{ route('admin.audits.ongoing') }}" class="nav-link {{ request()->routeIs(['admin.audits.ongoing', 'admin.audits.lobby']) || (request()->routeIs('admin.audits.show') && optional(request()->route('audit'))->isOpen()) ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0"/></svg>
                Ongoing
                @php($ongoingAuditCount = \App\Models\PlanterAudit::query()->where('is_current', true)->whereIn('status', [\App\Models\PlanterAudit::STATUS_QUEUED, \App\Models\PlanterAudit::STATUS_IN_PROGRESS, \App\Models\PlanterAudit::STATUS_IN_REVIEW])->count())
                @if ($ongoingAuditCount > 0)
                    <span class="ml-auto rounded-full bg-tan px-2 py-0.5 text-[11px] font-bold text-forest-dark">{{ $ongoingAuditCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.audits.passed') }}" class="nav-link {{ request()->routeIs('admin.audits.passed') || (request()->routeIs('admin.audits.show') && in_array(optional(request()->route('audit'))->status, [\App\Models\PlanterAudit::STATUS_PASSED, \App\Models\PlanterAudit::STATUS_CONDITIONAL], true)) ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0"/></svg>
                Passed
            </a>
            <a href="{{ route('admin.audits.rejected') }}" class="nav-link {{ request()->routeIs('admin.audits.rejected') || (request()->routeIs('admin.audits.show') && optional(request()->route('audit'))->status === \App\Models\PlanterAudit::STATUS_FAILED) ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                Rejected
            </a>
            @if (auth('web')->user()->isAdmin())
                <p class="px-3 pb-2 pt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-tan">Master data</p>
                <a href="{{ route('admin.districts.index') }}" class="nav-link {{ request()->routeIs('admin.districts.*') ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 0 1 3 16.382V5.618a1 1 0 0 1 1.447-.894L9 7m0 13 6-3m-6 3V7m6 10 5.447 2.724A1 1 0 0 0 21 18.382V7.618a1 1 0 0 0-1.447-.894L15 9m0 8V9m0 0L9 7"/></svg>
                    Districts
                </a>
                <a href="{{ route('admin.rdo-divisions.index') }}" class="nav-link {{ request()->routeIs('admin.rdo-divisions.*') ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                    RDO divisions
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8m13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    System users
                </a>
            @endif
            <p class="px-3 pb-2 pt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-tan">System info</p>
            <a href="{{ route('admin.about-system') }}" class="nav-link {{ request()->routeIs('admin.about-system') ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25h.008v.008H11.25zm0 3.75h.008v.008H11.25zM12 2.25a9.75 9.75 0 1 0 0 19.5 9.75 9.75 0 0 0 0-19.5z"/></svg>
                About System
            </a>
            <a href="{{ route('admin.changelog') }}" class="nav-link {{ request()->routeIs('admin.changelog') ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0"/></svg>
                Changelog
                <span class="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-[11px] font-bold text-sand">v{{ config('app.version') }}</span>
            </a>
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
