@extends('layouts.app')

@section('title', 'Approval lobby')
@section('heading', 'Planter approval lobby')
@section('subheading', 'Review pending registrations waiting for approval')

@section('actions')
    <a href="{{ route('admin.planters.index') }}" class="btn-secondary">All planters</a>
@endsection

@section('content')
    <div class="mb-5 grid gap-4 sm:grid-cols-3">
        <article class="card p-5">
            <p class="text-sm text-muted">Waiting for approval</p>
            <p class="mt-2 text-3xl font-semibold text-bark">{{ $stats['total'] }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Online registrations</p>
            <p class="mt-2 text-3xl font-semibold text-forest">{{ $stats['online'] }}</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-muted">Offline registrations</p>
            <p class="mt-2 text-3xl font-semibold text-tan">{{ $stats['offline'] }}</p>
        </article>
    </div>

    <form method="GET" action="{{ route('admin.planters.approval-lobby') }}" class="card mb-5 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, NIC, phone, or temporary ID" class="input-field sm:col-span-2">
        <select name="district" class="input-field">
            <option value="">All districts</option>
            @foreach (\App\Models\Planter::districts() as $district)
                <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="registration_type" class="input-field">
                <option value="">All types</option>
                @foreach (\App\Models\Planter::registrationTypes() as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['registration_type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary shrink-0">Filter</button>
        </div>
    </form>

    @if ($planters->isEmpty())
        <section class="card px-5 py-16 text-center">
            <p class="text-lg font-semibold text-forest-dark">No pending applications</p>
            <p class="mt-2 text-sm text-muted">New planter registrations will appear here until they are approved or rejected.</p>
        </section>
    @else
        <div class="space-y-4">
            @foreach ($planters as $planter)
                <article class="card overflow-hidden">
                    <div class="flex flex-col gap-4 border-b border-sand bg-cream px-5 py-4 sm:flex-row sm:items-start sm:justify-between sm:px-6">
                        <div>
                            <p class="font-mono text-xs font-semibold text-forest">{{ $planter->temporary_id }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-forest-dark">{{ $planter->name }}</h2>
                            <p class="mt-1 text-sm text-muted">
                                {{ $planter->nic }} · {{ $planter->phone }}
                                @if ($planter->email)
                                    · {{ $planter->email }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-sand px-2.5 py-1 text-xs font-semibold text-bark">
                                {{ \App\Models\Planter::registrationTypes()[$planter->registration_type] ?? 'Online' }}
                            </span>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-muted">{{ $planter->district }}</span>
                            <span class="text-xs text-muted">Submitted {{ sl_datetime($planter->created_at) }}</span>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Address</p>
                            <p class="mt-1 text-sm">{{ $planter->address ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Application document</p>
                            <p class="mt-1 text-sm">
                                @if ($planter->hasApplicationDocument())
                                    <a href="{{ route('admin.planters.document', $planter) }}" class="font-semibold text-leaf hover:text-forest">Download submitted form</a>
                                @else
                                    Online registration — no upload
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 border-t border-sand bg-paper px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.planters.show', $planter) }}" class="btn-secondary">Review details</a>
                            <form method="POST" action="{{ route('admin.planters.approve', $planter) }}">
                                @csrf
                                <button type="submit" class="btn-primary">Approve</button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('admin.planters.reject', $planter) }}" class="flex w-full flex-col gap-2 sm:max-w-xl sm:flex-row">
                            @csrf
                            <input name="rejection_reason" type="text" required class="input-field" placeholder="Rejection reason (required to reject)">
                            <button type="submit" class="btn-danger shrink-0">Reject</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($planters->hasPages())
            <div class="mt-5">
                {{ $planters->links() }}
            </div>
        @endif
    @endif
@endsection
