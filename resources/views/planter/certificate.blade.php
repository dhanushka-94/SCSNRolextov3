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
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <img src="{{ route('planter.certificate.qr') }}" alt="Certificate QR code" class="h-36 w-36 rounded-xl bg-white p-2 shadow-sm ring-1 ring-sand">
                <p class="text-sm text-muted">This QR code belongs only to this certificate. Anyone can scan it to check that the certificate is genuine and still valid.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('planter.certificate.print') }}" class="btn-secondary" target="_blank">Print certificate</a>
                <a href="{{ route('planter.certificate.pdf') }}" class="btn-primary">Download PDF</a>
                <a href="{{ $certificate->verifyUrl() }}" class="btn-secondary" target="_blank" rel="noopener">Open public verify page</a>
                <a href="{{ route('planter.certificate.qr.download', 'png') }}" class="btn-secondary">Download certificate QR (PNG)</a>
                <a href="{{ route('planter.certificate.qr.download', 'svg') }}" class="btn-secondary">Download certificate QR (SVG)</a>
            </div>
        </div>
    </article>
@endsection
