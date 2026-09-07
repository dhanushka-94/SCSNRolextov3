@extends('layouts.app')

@section('title', $district->name)
@section('heading', 'District details')
@section('subheading', $district->name)

@section('actions')
    <a href="{{ route('admin.districts.edit', $district) }}" class="btn-primary">Edit</a>
@endsection

@section('content')
    <article class="card mx-auto max-w-3xl overflow-hidden">
        <div class="border-b border-sand bg-cream px-5 py-6 sm:px-8">
            <h2 class="text-xl font-semibold text-forest-dark">{{ $district->name }}</h2>
            <p class="mt-1 text-sm text-muted">SCSNR code {{ $district->code }}</p>
        </div>

        <dl class="grid gap-5 p-5 sm:grid-cols-2 sm:p-8">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Status</dt>
                <dd class="mt-1">
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $district->isActive() ? 'bg-leaf/10 text-forest' : 'bg-sand text-bark' }}">
                        {{ \App\Models\District::statuses()[$district->status] }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Registry entries</dt>
                <dd class="mt-1 text-sm">{{ $district->planters_count }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">RDO divisions ({{ $district->rdo_divisions_count }})</dt>
                <dd class="mt-2 space-y-2">
                    @forelse ($district->rdoDivisions as $division)
                        <a href="{{ route('admin.rdo-divisions.show', $division) }}" class="flex items-center justify-between rounded-xl border border-sand bg-paper px-3 py-2 text-sm hover:bg-cream">
                            <span>{{ $division->name }} <span class="font-mono text-xs text-muted">({{ $division->code }})</span></span>
                            <span class="text-xs text-muted">{{ \App\Models\RdoDivision::statuses()[$division->status] }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-muted">No divisions yet.</p>
                    @endforelse
                </dd>
            </div>
        </dl>
    </article>
@endsection
