@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Overview of system users and planter registrations')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="card p-5">
            <p class="text-sm text-muted">System users</p>
            <p class="mt-3 text-3xl font-semibold text-forest">{{ $totalUsers }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Active staff</p>
            <p class="mt-3 text-3xl font-semibold text-leaf">{{ $activeUsers }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Pending planters</p>
            <p class="mt-3 text-3xl font-semibold text-bark">{{ $pendingPlanters }}</p>
            @if ($pendingPlanters > 0)
                <a href="{{ route('admin.planters.approval-lobby') }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">Open approval lobby</a>
            @endif
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Approved planters</p>
            <p class="mt-3 text-3xl font-semibold text-tan">{{ $approvedPlanters }}</p>
        </article>
    </div>

    <section class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between border-b border-sand px-5 py-4">
            <h2 class="font-semibold text-forest-dark">Recent planter registrations</h2>
            <a href="{{ route('admin.planters.index') }}" class="text-sm font-semibold text-leaf hover:text-forest">View all</a>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-cream text-muted">
                    <tr>
                        <th class="px-5 py-3 font-medium">Temporary ID</th>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">District</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentPlanters as $planter)
                        <tr class="border-t border-sand">
                            <td class="px-5 py-3 font-mono text-xs font-semibold text-forest">{{ $planter->temporary_id }}</td>
                            <td class="px-5 py-3 font-medium">{{ $planter->name }}</td>
                            <td class="px-5 py-3 text-muted">{{ $planter->district }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $planter->isApproved() ? 'bg-leaf/10 text-forest' : ($planter->isRejected() ? 'bg-red-50 text-red-800' : 'bg-sand text-bark') }}">
                                    {{ \App\Models\Planter::statuses()[$planter->status] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-muted">No planter registrations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @forelse ($recentPlanters as $planter)
                <article class="rounded-xl border border-sand bg-cream p-4">
                    <p class="font-mono text-xs font-semibold text-forest">{{ $planter->temporary_id }}</p>
                    <p class="font-semibold">{{ $planter->name }}</p>
                    <p class="mt-1 text-xs text-bark">{{ $planter->district }} · {{ \App\Models\Planter::statuses()[$planter->status] }}</p>
                </article>
            @empty
                <p class="text-sm text-muted">No planter registrations yet.</p>
            @endforelse
        </div>
    </section>
@endsection
