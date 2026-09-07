@extends('layouts.planter')

@section('title', 'Certificate')
@section('heading', 'Your certificate')

@section('content')
    <article class="card overflow-hidden">
        <div class="border-b border-sand bg-cream px-5 py-5 sm:px-8">
            <p class="font-mono text-sm font-semibold text-forest">{{ $certificate->certificate_number }}</p>
            <h2 class="mt-1 text-lg font-semibold text-forest-dark">{{ $certificate->outcomeLabel() }}</h2>
            <p class="mt-1 text-sm text-muted">Issued {{ $certificate->issued_at ? sl_datetime($certificate->issued_at) : '—' }}</p>
        </div>
        <div class="space-y-4 p-5 sm:p-8">
            <p class="text-sm text-muted">Your QR code now opens the public verification page for this certificate.</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('planter.certificate.print') }}" class="btn-primary" target="_blank">Print certificate</a>
                <a href="{{ $certificate->verifyUrl() }}" class="btn-secondary" target="_blank" rel="noopener">Open public verify page</a>
                <a href="{{ route('planter.profile.qr.download', 'png') }}" class="btn-secondary">Download QR</a>
            </div>
        </div>
    </article>
@endsection
