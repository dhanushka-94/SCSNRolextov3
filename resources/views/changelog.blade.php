@extends('layouts.app')

@section('title', 'Changelog')
@section('heading', 'Changelog')
@section('subheading', 'Release history and version updates for '.$currentVersion)

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <section class="card overflow-hidden">
            <div class="border-b border-sand bg-forest-dark px-5 py-6 text-white sm:px-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan">Current version</p>
                <div class="mt-3 flex flex-wrap items-end gap-3">
                    <p class="font-display text-4xl font-semibold tracking-tight sm:text-5xl">v{{ $currentVersion }}</p>
                    <p class="pb-1 text-sm text-sand/80">{{ config('app.short_name') }} · {{ config('app.full_name') }}</p>
                </div>
            </div>
        </section>

        @forelse ($releases as $release)
            @php
                $isCurrent = version_compare((string) $release['version'], (string) $currentVersion, '==');
                $typeStyles = [
                    'added' => 'bg-leaf/15 text-forest',
                    'changed' => 'bg-sand text-bark',
                    'fixed' => 'bg-tan/25 text-bark-dark',
                    'removed' => 'bg-red-50 text-red-800',
                    'security' => 'bg-forest/10 text-forest-dark',
                ];
            @endphp

            <article class="card overflow-hidden {{ $isCurrent ? 'ring-2 ring-leaf/40' : '' }}">
                <div class="flex flex-col gap-3 border-b border-sand bg-cream px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-display text-2xl font-semibold text-forest-dark">v{{ $release['version'] }}</h2>
                            @if ($isCurrent)
                                <span class="rounded-full bg-leaf px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-white">Current</span>
                            @endif
                        </div>
                        @if (! empty($release['title']))
                            <p class="mt-1 font-semibold text-ink">{{ $release['title'] }}</p>
                        @endif
                    </div>
                    <p class="text-sm text-muted">{{ \Illuminate\Support\Carbon::parse($release['date'])->format('d M Y') }}</p>
                </div>

                <ul class="space-y-3 px-5 py-5 sm:px-6">
                    @foreach ($release['changes'] as $change)
                        @php $type = strtolower($change['type'] ?? 'changed'); @endphp
                        <li class="flex gap-3 text-sm leading-6 text-ink">
                            <span class="mt-0.5 shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $typeStyles[$type] ?? $typeStyles['changed'] }}">
                                {{ $type }}
                            </span>
                            <span>{{ $change['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </article>
        @empty
            <section class="card p-8 text-center text-muted">
                No release notes have been published yet.
            </section>
        @endforelse
    </div>
@endsection
