@extends('layouts.app')

@section('title', 'RDO divisions')
@section('heading', 'RDO divisions')
@section('subheading', 'Rubber Development Officer divisions by district')

@section('actions')
    <a href="{{ route('admin.rdo-divisions.create') }}" class="btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
        <span class="hidden sm:inline">Add division</span>
    </a>
@endsection

@section('content')
    <form method="GET" action="{{ route('admin.rdo-divisions.index') }}" class="card mb-5 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search division, code, or district" class="input-field sm:col-span-2">
        <select name="district_id" class="input-field">
            <option value="">All districts</option>
            @foreach ($districts as $district)
                <option value="{{ $district->id }}" @selected((string) ($filters['district_id'] ?? '') === (string) $district->id)>{{ $district->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="status" class="input-field">
                <option value="">All statuses</option>
                @foreach (\App\Models\RdoDivision::statuses() as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
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
                        <th class="px-5 py-3 font-medium">Division</th>
                        <th class="px-5 py-3 font-medium">Code</th>
                        <th class="px-5 py-3 font-medium">District</th>
                        <th class="px-5 py-3 font-medium">Registry</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($divisions as $division)
                        <tr class="border-t border-sand">
                            <td class="px-5 py-4 font-semibold text-ink">{{ $division->name }}</td>
                            <td class="px-5 py-4 font-mono text-sm text-muted">{{ $division->code }}</td>
                            <td class="px-5 py-4 text-muted">{{ $division->district?->name ?: '—' }}@if($division->district) <span class="text-xs">({{ $division->district->code }})</span>@endif</td>
                            <td class="px-5 py-4 text-muted">{{ $division->planters_count }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $division->isActive() ? 'bg-leaf/10 text-forest' : 'bg-sand text-bark' }}">
                                    {{ \App\Models\RdoDivision::statuses()[$division->status] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.rdo-divisions.show', $division) }}" class="btn-secondary px-3 py-1.5">View</a>
                                    <a href="{{ route('admin.rdo-divisions.edit', $division) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                                    <form method="POST" action="{{ route('admin.rdo-divisions.destroy', $division) }}" data-delete-form data-delete-name="{{ $division->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger px-3 py-1.5">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-muted">No divisions match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @forelse ($divisions as $division)
                <article class="rounded-2xl border border-sand bg-cream p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $division->name }}</p>
                            <p class="text-sm text-muted">{{ $division->code }} · {{ $division->district?->name ?: '—' }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $division->isActive() ? 'bg-leaf/10 text-forest' : 'bg-sand text-bark' }}">
                            {{ \App\Models\RdoDivision::statuses()[$division->status] }}
                        </span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.rdo-divisions.show', $division) }}" class="btn-secondary px-3 py-1.5">View</a>
                        <a href="{{ route('admin.rdo-divisions.edit', $division) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                    </div>
                </article>
            @empty
                <p class="py-6 text-center text-sm text-muted">No divisions match the current filters.</p>
            @endforelse
        </div>

        @if ($divisions->hasPages())
            <div class="border-t border-sand px-4 py-3">
                {{ $divisions->links() }}
            </div>
        @endif
    </section>
@endsection
