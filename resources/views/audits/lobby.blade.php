@extends('layouts.app')

@php
    $titles = [
        'ongoing' => ['Ongoing audits', 'Queued, in progress, and in review'],
        'passed' => ['Passed audits', 'Passed and conditional outcomes'],
        'rejected' => ['Rejected audits', 'Failed audit attempts (including archived)'],
    ];
    [$heading, $subheading] = $titles[$bucket] ?? $titles['ongoing'];
@endphp

@section('title', $heading)
@section('heading', $heading)
@section('subheading', $subheading)

@section('actions')
    <a href="{{ route('admin.planters.index') }}" class="btn-secondary">Registry</a>
@endsection

@section('content')
    <div class="mb-5 flex flex-wrap gap-2">
        @if ($canDispatch || auth('web')->user()->canViewAuditRound(\App\Models\PlanterAudit::ROUND_FIRST))
            <a href="{{ route($listRoute, ['round' => 'first'] + request()->except('round', 'page')) }}"
               class="rounded-full px-4 py-2 text-sm font-semibold {{ $round === 'first' ? 'bg-forest text-white' : 'bg-sand text-bark hover:bg-line' }}">
                First Audit
            </a>
        @endif
        @if ($canDispatch || auth('web')->user()->canViewAuditRound(\App\Models\PlanterAudit::ROUND_FINAL))
            <a href="{{ route($listRoute, ['round' => 'final'] + request()->except('round', 'page')) }}"
               class="rounded-full px-4 py-2 text-sm font-semibold {{ $round === 'final' ? 'bg-forest text-white' : 'bg-sand text-bark hover:bg-line' }}">
                Final Audit
            </a>
        @endif
    </div>

    <div class="mb-5 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('admin.audits.ongoing', ['round' => $round]) }}" class="card p-5 transition {{ $bucket === 'ongoing' ? 'border-leaf/50 ring-1 ring-leaf/30' : 'hover:border-leaf/40' }}">
            <p class="text-sm text-muted">Ongoing</p>
            <p class="mt-2 text-3xl font-semibold text-bark">{{ $stats['ongoing'] }}</p>
        </a>
        <a href="{{ route('admin.audits.passed', ['round' => $round]) }}" class="card p-5 transition {{ $bucket === 'passed' ? 'border-leaf/50 ring-1 ring-leaf/30' : 'hover:border-leaf/40' }}">
            <p class="text-sm text-muted">Passed</p>
            <p class="mt-2 text-3xl font-semibold text-leaf">{{ $stats['passed'] }}</p>
        </a>
        <a href="{{ route('admin.audits.rejected', ['round' => $round]) }}" class="card p-5 transition {{ $bucket === 'rejected' ? 'border-leaf/50 ring-1 ring-leaf/30' : 'hover:border-leaf/40' }}">
            <p class="text-sm text-muted">Rejected</p>
            <p class="mt-2 text-3xl font-semibold text-red-800">{{ $stats['rejected'] }}</p>
        </a>
    </div>

    <form method="GET" action="{{ route($listRoute) }}" class="card mb-5 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="hidden" name="round" value="{{ $round }}">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search ID, name, NIC, or email" class="input-field sm:col-span-2">
        <select name="district" class="input-field">
            <option value="">All districts</option>
            @foreach (\App\Models\District::query()->orderBy('name')->pluck('name') as $district)
                <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    @if ($audits->isEmpty())
        <section class="card px-5 py-16 text-center">
            <p class="text-lg font-semibold text-forest-dark">No {{ strtolower($heading) }}</p>
            <p class="mt-2 text-sm text-muted">
                @if ($bucket === 'ongoing')
                    Approved registry entries appear here after staff send them to this audit round.
                @elseif ($bucket === 'passed')
                    Completed pass and conditional results will show here.
                @else
                    Failed audit attempts will show here.
                @endif
            </p>
        </section>
    @else
        <div class="space-y-4">
            @foreach ($audits as $audit)
                <article class="card overflow-hidden">
                    <div class="flex flex-col gap-3 border-b border-sand bg-cream px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <p class="font-mono text-xs font-semibold text-forest">{{ $audit->planter->identification_number ?: $audit->planter->temporary_id }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-forest-dark">{{ $audit->planter->name }}</h2>
                            <p class="mt-1 text-sm text-muted">{{ $audit->planter->district ?: '—' }} · {{ $audit->planter->rdd_division ?: '—' }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-tan/25 px-2.5 py-1 text-xs font-semibold text-bark-dark">{{ $audit->statusLabel() }}</span>
                            <span class="rounded-full bg-sand px-2.5 py-1 text-xs font-semibold text-bark">Attempt #{{ $audit->attempt_number }}</span>
                            @unless ($audit->is_current)
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-900">Archived</span>
                            @endunless
                            @if ($bucket === 'ongoing' && $audit->sent_at)
                                <span class="text-xs text-muted">Sent {{ sl_datetime($audit->sent_at) }}</span>
                            @elseif ($audit->completed_at)
                                <span class="text-xs text-muted">Completed {{ sl_datetime($audit->completed_at) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <p class="text-sm text-muted">
                            @if ($bucket === 'ongoing')
                                Assignee: <span class="font-semibold text-ink">{{ $audit->assignee?->name ?? 'Unassigned' }}</span>
                            @else
                                Completed by: <span class="font-semibold text-ink">{{ $audit->completer?->name ?? '—' }}</span>
                                @if ($audit->outcome_notes)
                                    <span class="mt-1 block text-ink">{{ \Illuminate\Support\Str::limit($audit->outcome_notes, 120) }}</span>
                                @endif
                            @endif
                        </p>
                        <a href="{{ route('admin.audits.show', $audit) }}" class="btn-primary">
                            {{ $bucket === 'ongoing' ? 'Open checklist' : 'View checklist' }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($audits->hasPages())
            <div class="mt-5">{{ $audits->links() }}</div>
        @endif
    @endif
@endsection
