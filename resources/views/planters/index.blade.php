@extends('layouts.app')

@php
    $isRejectedList = ($listStatus ?? \App\Models\Planter::STATUS_APPROVED) === \App\Models\Planter::STATUS_REJECTED;
    $listFormRoute = $listRoute ?? 'admin.planters.index';
@endphp

@section('title', $isRejectedList ? 'Rejected' : 'Registry')
@section('heading', $isRejectedList ? 'Rejected' : 'Registry')
@section('subheading', $isRejectedList
    ? 'Applications that were reviewed and rejected'
    : 'Approved registration records')

@section('actions')
    @if ($isRejectedList)
        <a href="{{ route('admin.planters.index') }}" class="btn-secondary">Registry</a>
    @else
        <a href="{{ route('admin.planters.rejected') }}" class="btn-secondary">Rejected</a>
        <a href="{{ route('admin.planters.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            <span class="hidden sm:inline">Add entry</span>
        </a>
    @endif
@endsection

@section('content')
    <form method="GET" action="{{ route($listFormRoute) }}" class="card mb-5 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search ID, name, NIC, or email" class="input-field sm:col-span-2">
        <div class="flex gap-2">
            <select name="district" class="input-field">
                <option value="">All districts</option>
                @foreach (\App\Models\District::query()->orderBy('name')->pluck('name') as $district)
                    <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary shrink-0">Filter</button>
        </div>
    </form>

    <section class="card overflow-hidden">
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-cream text-muted">
                    <tr>
                        <th class="px-5 py-3 font-medium">Identification</th>
                        <th class="px-5 py-3 font-medium">Applicant</th>
                        <th class="px-5 py-3 font-medium">District</th>
                        @if ($isRejectedList)
                            <th class="px-5 py-3 font-medium">Rejection reason</th>
                        @else
                            <th class="px-5 py-3 font-medium">Status</th>
                        @endif
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($planters as $planter)
                        <tr class="border-t border-sand">
                            <td class="px-5 py-4 font-mono text-xs font-semibold text-forest">
                                {{ $planter->identification_number ?: '—' }}
                                @unless ($planter->identification_number)
                                    <span class="mt-1 block font-mono text-[11px] text-muted">{{ $planter->temporary_id }}</span>
                                @endunless
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-ink">{{ $planter->name }}</p>
                                <p class="text-xs text-muted">{{ $planter->nic }} · {{ $planter->email ?: 'No email' }} · {{ \App\Models\Planter::registrationTypes()[$planter->registration_type] ?? 'Online' }}</p>
                            </td>
                            <td class="px-5 py-4 text-muted">{{ $planter->district }}</td>
                            <td class="px-5 py-4">
                                @if ($isRejectedList)
                                    <p class="text-sm text-red-800">{{ $planter->rejection_reason ?: '—' }}</p>
                                @else
                                    <span class="rounded-full bg-leaf/10 px-2.5 py-1 text-xs font-semibold text-forest">
                                        {{ \App\Models\Planter::statuses()[$planter->status] }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.planters.show', $planter) }}" class="btn-secondary px-3 py-1.5">View</a>
                                    <a href="{{ route('admin.planters.edit', $planter) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                                    <form method="POST" action="{{ route('admin.planters.destroy', $planter) }}" data-delete-form data-delete-name="{{ $planter->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger px-3 py-1.5">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-muted">
                                {{ $isRejectedList ? 'No rejected records match the current filters.' : 'No approved registry records match the current filters.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @forelse ($planters as $planter)
                <article class="rounded-2xl border border-sand bg-cream p-4">
                    <p class="font-mono text-xs font-semibold text-forest">{{ $planter->identification_number ?: $planter->temporary_id }}</p>
                    <p class="mt-1 font-semibold">{{ $planter->name }}</p>
                    <p class="text-sm text-muted">{{ $planter->district }}</p>
                    @if ($isRejectedList)
                        <p class="mt-2 text-sm text-red-800">{{ $planter->rejection_reason ?: 'No rejection reason recorded.' }}</p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.planters.show', $planter) }}" class="btn-secondary px-3 py-1.5">View</a>
                        <a href="{{ route('admin.planters.edit', $planter) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                    </div>
                </article>
            @empty
                <p class="py-6 text-center text-sm text-muted">
                    {{ $isRejectedList ? 'No rejected records match the current filters.' : 'No approved registry records match the current filters.' }}
                </p>
            @endforelse
        </div>

        @if ($planters->hasPages())
            <div class="border-t border-sand px-4 py-3">
                {{ $planters->links() }}
            </div>
        @endif
    </section>
@endsection
