@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Overview of registrations, approvals, and system activity')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-9">
        <article class="card p-5">
            <p class="text-sm text-muted">Pending approval</p>
            <p class="mt-3 text-3xl font-semibold text-bark">{{ $pendingPlanters }}</p>
            @if ($pendingPlanters > 0)
                <a href="{{ route('admin.planters.registration-lobby') }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">Open Registration Lobby</a>
            @endif
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Pending online</p>
            <p class="mt-3 text-3xl font-semibold text-forest">{{ $pendingOnline }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Pending offline</p>
            <p class="mt-3 text-3xl font-semibold text-tan">{{ $pendingOffline }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Approved in registry</p>
            <p class="mt-3 text-3xl font-semibold text-leaf">{{ $approvedPlanters }}</p>
            <a href="{{ route('admin.planters.index') }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">Open Registry</a>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Rejected</p>
            <p class="mt-3 text-3xl font-semibold text-red-800">{{ $rejectedPlanters }}</p>
            <a href="{{ route('admin.planters.rejected') }}" class="mt-3 inline-flex text-sm font-semibold text-red-800 hover:text-red-900">View rejected</a>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Active audits</p>
            <p class="mt-3 text-3xl font-semibold text-bark">{{ $activeAudits }}</p>
            <a href="{{ route('admin.audits.ongoing') }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">Open Ongoing audits</a>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Completed audits</p>
            <p class="mt-3 text-3xl font-semibold text-forest">{{ $completedAudits }}</p>
            <a href="{{ route('admin.audits.passed') }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">View Passed</a>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Issued certificates</p>
            <p class="mt-3 text-3xl font-semibold text-leaf">{{ $issuedCertificates }}</p>
            <a href="{{ route('admin.certificates.issued') }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">View Issued</a>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Active staff</p>
            <p class="mt-3 text-3xl font-semibold text-forest-dark">{{ $activeUsers }}</p>
            <p class="mt-1 text-xs text-muted">{{ $totalUsers }} system users</p>
        </article>
    </div>

    <section class="mt-6 grid gap-4 lg:grid-cols-3 xl:grid-cols-6">
        <a href="{{ route('admin.planters.registration-lobby') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-leaf/10 text-leaf">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">Registration Lobby</span>
                <span class="text-sm text-muted">Review pending applications</span>
            </span>
        </a>
        <a href="{{ route('admin.planters.index') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sand text-bark">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M5 21V7.5L12 3l7 4.5V21M9 21v-6h6v6"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">Registry</span>
                <span class="text-sm text-muted">Approved records only</span>
            </span>
        </a>
        <a href="{{ route('admin.audits.ongoing') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-leaf/10 text-leaf">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">Ongoing audits</span>
                <span class="text-sm text-muted">Queued and in-progress work</span>
            </span>
        </a>
        <a href="{{ route('admin.audits.passed') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-leaf/10 text-leaf">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">Passed audits</span>
                <span class="text-sm text-muted">Passed &amp; conditional results</span>
            </span>
        </a>
        <a href="{{ route('admin.audits.rejected') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">Rejected audits</span>
                <span class="text-sm text-muted">Failed audit attempts</span>
            </span>
        </a>
        <a href="{{ route('admin.planters.rejected') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">Rejected registry</span>
                <span class="text-sm text-muted">Declined applications</span>
            </span>
        </a>
        <a href="{{ route('admin.planters.create') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-tan/20 text-bark-dark">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">Add entry</span>
                <span class="text-sm text-muted">Create a registration manually</span>
            </span>
        </a>
    </section>

    <section class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-sand px-5 py-4">
            <h2 class="font-semibold text-forest-dark">Recent approved entries</h2>
            <a href="{{ route('admin.planters.index') }}" class="text-sm font-semibold text-leaf hover:text-forest">View all</a>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-cream text-muted">
                    <tr>
                        <th class="px-5 py-3 font-medium">SCSNR ID</th>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">District</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPlanters as $planter)
                        <tr class="border-t border-sand">
                            <td class="px-5 py-3 font-mono text-xs font-semibold text-forest">{{ $planter->identification_number }}</td>
                            <td class="px-5 py-3 font-medium">{{ $planter->name }}</td>
                            <td class="px-5 py-3 text-muted">{{ $planter->district ?: '—' }}</td>
                            <td class="px-5 py-3 text-muted">{{ \App\Models\Planter::registrationTypes()[$planter->registration_type] ?? 'Online' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $planter->isApproved() ? 'bg-leaf/10 text-forest' : ($planter->isRejected() ? 'bg-red-50 text-red-800' : 'bg-sand text-bark') }}">
                                    {{ \App\Models\Planter::statuses()[$planter->status] }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.planters.show', $planter) }}" class="font-semibold text-leaf hover:text-forest">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-muted">No registry entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @forelse ($recentPlanters as $planter)
                <article class="rounded-xl border border-sand bg-cream p-4">
                    <p class="font-mono text-xs font-semibold text-forest">{{ $planter->identification_number }}</p>
                    <p class="font-semibold">{{ $planter->name }}</p>
                    <p class="mt-1 text-xs text-bark">{{ $planter->district ?: '—' }} · {{ \App\Models\Planter::statuses()[$planter->status] }}</p>
                    <a href="{{ route('admin.planters.show', $planter) }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">View application</a>
                </article>
            @empty
                <p class="text-sm text-muted">No registry entries yet.</p>
            @endforelse
        </div>
    </section>
@endsection
