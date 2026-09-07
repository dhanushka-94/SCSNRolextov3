@extends('layouts.app')

@section('title', $division->name)
@section('heading', 'RDO division details')
@section('subheading', $division->name)

@section('actions')
    <a href="{{ route('admin.rdo-divisions.edit', $division) }}" class="btn-primary">Edit</a>
@endsection

@section('content')
    <article class="card mx-auto max-w-3xl overflow-hidden">
        <div class="border-b border-sand bg-cream px-5 py-6 sm:px-8">
            <h2 class="text-xl font-semibold text-forest-dark">{{ $division->name }}</h2>
            <p class="mt-1 text-sm text-muted">Code {{ $division->code }} · {{ $division->district?->name ?: 'No district' }}</p>
        </div>

        <dl class="grid gap-5 p-5 sm:grid-cols-2 sm:p-8">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">District</dt>
                <dd class="mt-1 text-sm">
                    @if ($division->district)
                        <a href="{{ route('admin.districts.show', $division->district) }}" class="font-medium text-forest hover:underline">{{ $division->district->name }} ({{ $division->district->code }})</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Division code</dt>
                <dd class="mt-1 font-mono text-sm">{{ $division->code }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Status</dt>
                <dd class="mt-1">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $division->isActive() ? 'bg-leaf/10 text-forest' : 'bg-sand text-bark' }}">
                        {{ \App\Models\RdoDivision::statuses()[$division->status] }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Linked registry entries</dt>
                <dd class="mt-1 text-sm">{{ $division->planters_count }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Updated</dt>
                <dd class="mt-1 text-sm">{{ sl_datetime($division->updated_at) }}</dd>
            </div>
        </dl>
    </article>
@endsection
