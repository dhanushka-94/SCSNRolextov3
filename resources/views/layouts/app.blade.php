<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('SPPNR-logo.png') }}">
    <x-app-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-cream">
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-bark-dark/40 lg:hidden"></div>

    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-forest-dark text-white transition-transform duration-200 lg:translate-x-0">
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
            <img src="{{ asset('SPPNR-logo.png') }}" alt="{{ config('app.short_name') }}" class="h-12 w-12 rounded-full bg-white object-contain p-1 shadow-sm">
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

<?php
                $navUser = auth('web')->user();
                $navShowAudit = optional(request()->route('audit'));
                $navFirstOngoing = \App\Models\PlanterAudit::query()
                    ->where('round', \App\Models\PlanterAudit::ROUND_FIRST)
                    ->where('is_current', true)
                    ->whereIn('status', [
                        \App\Models\PlanterAudit::STATUS_QUEUED,
                        \App\Models\PlanterAudit::STATUS_IN_PROGRESS,
                        \App\Models\PlanterAudit::STATUS_IN_REVIEW,
                    ])->count();
                $navSecondOngoing = \App\Models\PlanterAudit::query()
                    ->where('round', \App\Models\PlanterAudit::ROUND_FINAL)
                    ->where('is_current', true)
                    ->whereIn('status', [
                        \App\Models\PlanterAudit::STATUS_QUEUED,
                        \App\Models\PlanterAudit::STATUS_IN_PROGRESS,
                        \App\Models\PlanterAudit::STATUS_IN_REVIEW,
                    ])->count();
                $navRound = request('round');
                if (! in_array($navRound, ['first', 'final'], true) && $navShowAudit) {
                    $navRound = $navShowAudit->round;
                }
            ?>

            @if ($navUser->canViewAuditRound(\App\Models\PlanterAudit::ROUND_FIRST))
                <p class="px-3 pb-2 pt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-tan">First Audit</p>
                <a href="{{ route('admin.audits.ongoing', ['round' => 'first']) }}" class="nav-link {{ (request()->routeIs(['admin.audits.ongoing', 'admin.audits.lobby']) && ($navRound === 'first' || $navRound === null)) || (request()->routeIs('admin.audits.show') && $navShowAudit?->round === 'first' && $navShowAudit?->isOpen()) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0"/></svg>
                    Ongoing
                    @if ($navFirstOngoing > 0)
                        <span class="ml-auto rounded-full bg-tan px-2 py-0.5 text-[11px] font-bold text-forest-dark">{{ $navFirstOngoing }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.audits.passed', ['round' => 'first']) }}" class="nav-link {{ (request()->routeIs('admin.audits.passed') && $navRound === 'first') || (request()->routeIs('admin.audits.show') && $navShowAudit?->round === 'first' && in_array($navShowAudit?->status, [\App\Models\PlanterAudit::STATUS_PASSED, \App\Models\PlanterAudit::STATUS_CONDITIONAL], true)) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0"/></svg>
                    Passed
                </a>
                <a href="{{ route('admin.audits.rejected', ['round' => 'first']) }}" class="nav-link {{ (request()->routeIs('admin.audits.rejected') && $navRound === 'first') || (request()->routeIs('admin.audits.show') && $navShowAudit?->round === 'first' && $navShowAudit?->status === \App\Models\PlanterAudit::STATUS_FAILED) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    Rejected
                </a>
            @endif

            @if ($navUser->canViewAuditRound(\App\Models\PlanterAudit::ROUND_FINAL))
                <p class="px-3 pb-2 pt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-tan">Second Audit</p>
                <a href="{{ route('admin.audits.ongoing', ['round' => 'final']) }}" class="nav-link {{ (request()->routeIs(['admin.audits.ongoing', 'admin.audits.lobby']) && $navRound === 'final') || (request()->routeIs('admin.audits.show') && $navShowAudit?->round === 'final' && $navShowAudit?->isOpen()) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0"/></svg>
                    Ongoing
                    @if ($navSecondOngoing > 0)
                        <span class="ml-auto rounded-full bg-tan px-2 py-0.5 text-[11px] font-bold text-forest-dark">{{ $navSecondOngoing }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.audits.passed', ['round' => 'final']) }}" class="nav-link {{ (request()->routeIs('admin.audits.passed') && $navRound === 'final') || (request()->routeIs('admin.audits.show') && $navShowAudit?->round === 'final' && in_array($navShowAudit?->status, [\App\Models\PlanterAudit::STATUS_PASSED, \App\Models\PlanterAudit::STATUS_CONDITIONAL], true)) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0"/></svg>
                    Passed
                </a>
                <a href="{{ route('admin.audits.rejected', ['round' => 'final']) }}" class="nav-link {{ (request()->routeIs('admin.audits.rejected') && $navRound === 'final') || (request()->routeIs('admin.audits.show') && $navShowAudit?->round === 'final' && $navShowAudit?->status === \App\Models\PlanterAudit::STATUS_FAILED) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    Rejected
                </a>
            @endif

            @if (auth('web')->user()->canManageCertificates())
                <p class="px-3 pb-2 pt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-tan">Certificates</p>
                <a href="{{ route('admin.certificates.issued') }}" class="nav-link {{ request()->routeIs('admin.certificates.issued') || (request()->routeIs(['admin.certificates.show', 'admin.certificates.print']) && optional(request()->route('certificate'))?->isIssued()) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M19.5 14.25v4.5a2.25 2.25 0 0 1-2.25 2.25h-10.5A2.25 2.25 0 0 1 4.5 18.75v-4.5m15 0V6.75A2.25 2.25 0 0 0 17.25 4.5h-10.5A2.25 2.25 0 0 0 4.5 6.75v7.5m15 0h-15"/></svg>
                    Issued
                    @php($issuedCertCount = \App\Models\Certificate::query()->where('status', \App\Models\Certificate::STATUS_ISSUED)->count())
                    @if ($issuedCertCount > 0)
                        <span class="ml-auto rounded-full bg-tan px-2 py-0.5 text-[11px] font-bold text-forest-dark">{{ $issuedCertCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.certificates.revoked') }}" class="nav-link {{ request()->routeIs('admin.certificates.revoked') || (request()->routeIs(['admin.certificates.show', 'admin.certificates.print']) && optional(request()->route('certificate'))?->isRevoked()) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    Revoked
                </a>
            @endif
            @if (auth('web')->user()->canManageMasterData())
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
            @if (auth('web')->user()->canManageSettings())
                <p class="px-3 pb-2 pt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-tan">Developer</p>
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs(['admin.settings.index', 'admin.settings.maintenance']) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                    Settings
                    @if (\App\Models\SystemSetting::maintenanceEnabled())
                        <span class="ml-auto rounded-full bg-amber-400 px-2 py-0.5 text-[11px] font-bold text-forest-dark">Maint.</span>
                    @endif
                </a>
                <a href="{{ route('admin.settings.api-docs') }}" class="nav-link {{ request()->routeIs('admin.settings.api-docs') ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>
                    API documentation
                </a>
            @endif
            <p class="px-3 pb-2 pt-4 text-[11px] font-semibold uppercase tracking-[0.16em] text-tan">System info</p>
            <a href="{{ route('admin.about-system') }}" class="nav-link {{ request()->routeIs('admin.about-system') ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25h.008v.008H11.25zm0 3.75h.008v.008H11.25zM12 2.25a9.75 9.75 0 1 0 0 19.5 9.75 9.75 0 0 0 0-19.5z"/></svg>
                About System
            </a>
            <a href="{{ route('admin.sms-templates.index') }}" class="nav-link {{ request()->routeIs('admin.sms-templates.*') ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75h6.75M8.625 12.75h4.5m-7.5 6.75 1.886-3.772A4.5 4.5 0 0 1 11.014 14.25h1.972a4.5 4.5 0 0 1 4.003 2.478L19.5 19.5M6.75 19.5h10.5A2.25 2.25 0 0 0 19.5 17.25V6.75A2.25 2.25 0 0 0 17.25 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5A2.25 2.25 0 0 0 6.75 19.5z"/></svg>
                SMS templates
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
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 border-b border-sand/60 bg-forest-dark/5 px-4 py-1.5 text-xs text-bark sm:px-6">
                <span>Hello! <span class="font-semibold text-forest-dark">{{ auth('web')->user()->name }}</span> - as <span class="font-semibold text-forest-dark">{{ \App\Models\User::roles()[auth('web')->user()->role] ?? auth('web')->user()->role }}</span></span>
                <span class="text-muted">Sri Lanka time: <x-sl-clock class="font-semibold text-forest-dark" /></span>
            </div>
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
