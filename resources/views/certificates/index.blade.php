@extends('layouts.app')

@php
    $isRevoked = $status === \App\Models\Certificate::STATUS_REVOKED;
    $heading = $isRevoked ? 'Revoked certificates' : 'Issued certificates';
@endphp

@section('title', $heading)
@section('heading', $heading)
@section('subheading', $isRevoked ? 'Revoked SCSNR certificates' : 'Active SCSNR certificates')

@section('actions')
    <a href="{{ route('admin.planters.index') }}" class="btn-secondary">Registry</a>
@endsection

@section('content')
    <div class="mb-5 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('admin.certificates.issued') }}" class="card p-5 transition {{ ! $isRevoked ? 'border-leaf/50 ring-1 ring-leaf/30' : 'hover:border-leaf/40' }}">
            <p class="text-sm text-muted">Issued</p>
            <p class="mt-2 text-3xl font-semibold text-leaf">{{ $stats['issued'] }}</p>
        </a>
        <a href="{{ route('admin.certificates.revoked') }}" class="card p-5 transition {{ $isRevoked ? 'border-leaf/50 ring-1 ring-leaf/30' : 'hover:border-leaf/40' }}">
            <p class="text-sm text-muted">Revoked</p>
            <p class="mt-2 text-3xl font-semibold text-red-800">{{ $stats['revoked'] }}</p>
        </a>
    </div>

    <form method="GET" action="{{ route($listRoute) }}" class="card mb-5 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search certificate, SCSNR ID, name" class="input-field sm:col-span-2">
        <select name="district" class="input-field">
            <option value="">All districts</option>
            @foreach (\App\Models\District::query()->orderBy('name')->pluck('name') as $district)
                <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    @if ($certificates->isEmpty())
        <section class="card px-5 py-16 text-center">
            <p class="text-lg font-semibold text-forest-dark">No {{ strtolower($heading) }}</p>
            <p class="mt-2 text-sm text-muted">
                {{ $isRevoked ? 'Revoked certificates will appear here.' : 'Issue a certificate from an approved planter after Final Audit pass/conditional.' }}
            </p>
        </section>
    @else
        <div class="space-y-4">
            @foreach ($certificates as $certificate)
                <article class="card overflow-hidden">
                    <div class="flex flex-col gap-3 border-b border-sand bg-cream px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <p class="font-mono text-xs font-semibold text-forest">{{ $certificate->certificate_number }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-forest-dark">{{ $certificate->planter->name }}</h2>
                            <p class="mt-1 text-sm text-muted">
                                {{ $certificate->planter->identification_number }}
                                · {{ $certificate->planter->district ?: '—' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-tan/25 px-2.5 py-1 text-xs font-semibold text-bark-dark">{{ $certificate->outcomeLabel() }}</span>
                            <span class="rounded-full bg-sand px-2.5 py-1 text-xs font-semibold text-bark">{{ $certificate->statusLabel() }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <p class="text-sm text-muted">
                            @if ($isRevoked)
                                Revoked {{ $certificate->revoked_at ? sl_datetime($certificate->revoked_at) : '—' }}
                                @if ($certificate->revoker) · {{ $certificate->revoker->name }} @endif
                            @else
                                Issued {{ $certificate->issued_at ? sl_datetime($certificate->issued_at) : '—' }}
                                @if ($certificate->issuer) · {{ $certificate->issuer->name }} @endif
                            @endif
                        </p>
                        <a href="{{ route('admin.certificates.show', $certificate) }}" class="btn-primary">Open</a>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($certificates->hasPages())
            <div class="mt-5">{{ $certificates->links() }}</div>
        @endif
    @endif
@endsection
