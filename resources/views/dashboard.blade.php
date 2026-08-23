@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Overview of registrations, approvals, and system activity')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <article class="card p-5">
            <p class="text-sm text-muted">Pending approval</p>
            <p class="mt-3 text-3xl font-semibold text-bark">{{ $pendingPlanters }}</p>
            @if ($pendingPlanters > 0)
                <a href="{{ route('admin.planters.approval-lobby') }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">Open approval lobby</a>
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
            <p class="text-sm text-muted">Approved planters</p>
            <p class="mt-3 text-3xl font-semibold text-leaf">{{ $approvedPlanters }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Rejected</p>
            <p class="mt-3 text-3xl font-semibold text-red-800">{{ $rejectedPlanters }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Active staff</p>
            <p class="mt-3 text-3xl font-semibold text-forest-dark">{{ $activeUsers }}</p>
            <p class="mt-1 text-xs text-muted">{{ $totalUsers }} system users</p>
        </article>
    </div>

    <section class="mt-6 grid gap-4 lg:grid-cols-3">
        <a href="{{ route('admin.planters.approval-lobby') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-leaf/10 text-leaf">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">Approval lobby</span>
                <span class="text-sm text-muted">Review pending applications</span>
            </span>
        </a>
        <a href="{{ route('admin.planters.index') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sand text-bark">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M5 21V7.5L12 3l7 4.5V21M9 21v-6h6v6"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">All planters</span>
                <span class="text-sm text-muted">Search and manage records</span>
            </span>
        </a>
        <a href="{{ route('admin.planters.create') }}" class="card flex items-center gap-4 p-5 transition hover:border-leaf/40 hover:shadow-md">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-tan/20 text-bark-dark">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-forest-dark">Add planter</span>
                <span class="text-sm text-muted">Create a registration manually</span>
            </span>
        </a>
    </section>

    <section class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-sand px-5 py-4">
            <h2 class="font-semibold text-forest-dark">Recent planter registrations</h2>
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
                        <tr><td colspan="6" class="px-5 py-8 text-center text-muted">No planter registrations yet.</td></tr>
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
                <p class="text-sm text-muted">No planter registrations yet.</p>
            @endforelse
        </div>
    </section>
@endsection
