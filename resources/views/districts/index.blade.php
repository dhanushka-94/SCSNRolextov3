@extends('layouts.app')

@section('title', 'Districts')
@section('heading', 'Districts')
@section('subheading', 'Manage districts used in the registry')

@section('actions')
    <a href="{{ route('admin.districts.create') }}" class="btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
        <span class="hidden sm:inline">Add district</span>
    </a>
@endsection

@section('content')
    <form method="GET" action="{{ route('admin.districts.index') }}" class="card mb-5 grid gap-3 p-4 sm:grid-cols-3">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name or code" class="input-field sm:col-span-2">
        <div class="flex gap-2">
            <select name="status" class="input-field">
                <option value="">All statuses</option>
                @foreach (\App\Models\District::statuses() as $value => $label)
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
                        <th class="px-5 py-3 font-medium">District</th>
                        <th class="px-5 py-3 font-medium">Code</th>
                        <th class="px-5 py-3 font-medium">RDO divisions</th>
                        <th class="px-5 py-3 font-medium">Registry</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($districts as $district)
                        <tr class="border-t border-sand">
                            <td class="px-5 py-4 font-semibold text-ink">{{ $district->name }}</td>
                            <td class="px-5 py-4 text-muted">{{ $district->code }}</td>
                            <td class="px-5 py-4 text-muted">{{ $district->rdo_divisions_count }}</td>
                            <td class="px-5 py-4 text-muted">{{ $district->planters_count }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $district->isActive() ? 'bg-leaf/10 text-forest' : 'bg-sand text-bark' }}">
                                    {{ \App\Models\District::statuses()[$district->status] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.districts.show', $district) }}" class="btn-secondary px-3 py-1.5">View</a>
                                    <a href="{{ route('admin.districts.edit', $district) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                                    <form method="POST" action="{{ route('admin.districts.destroy', $district) }}" data-delete-form data-delete-name="{{ $district->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger px-3 py-1.5">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-muted">No districts match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-3 p-4 md:hidden">
            @forelse ($districts as $district)
                <article class="rounded-2xl border border-sand bg-cream p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $district->name }}</p>
                            <p class="text-sm text-muted">Code {{ $district->code }} · {{ $district->rdo_divisions_count }} divisions</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $district->isActive() ? 'bg-leaf/10 text-forest' : 'bg-sand text-bark' }}">
                            {{ \App\Models\District::statuses()[$district->status] }}
                        </span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.districts.show', $district) }}" class="btn-secondary px-3 py-1.5">View</a>
                        <a href="{{ route('admin.districts.edit', $district) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                    </div>
                </article>
            @empty
                <p class="py-6 text-center text-sm text-muted">No districts match the current filters.</p>
            @endforelse
        </div>

        @if ($districts->hasPages())
            <div class="border-t border-sand px-4 py-3">
                {{ $districts->links() }}
            </div>
        @endif
    </section>
@endsection
