@extends('layouts.app')

@section('title', $certificate->certificate_number)
@section('heading', 'Certificate')
@section('subheading', $certificate->certificate_number)

@section('actions')
    <a href="{{ route($certificate->isRevoked() ? 'admin.certificates.revoked' : 'admin.certificates.issued') }}" class="btn-secondary">Back to list</a>
    <a href="{{ route('admin.planters.show', $certificate->planter) }}" class="btn-secondary">Registry details</a>
    <a href="{{ route('admin.certificates.print', $certificate) }}" class="btn-primary" target="_blank">Print</a>
@endsection

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        @error('certificate')
            <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</p>
        @enderror

        <section class="card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Status</p>
                    <p class="mt-1 text-xl font-semibold text-forest-dark">{{ $certificate->statusLabel() }} · {{ $certificate->outcomeLabel() }}</p>
                    <p class="mt-2 font-mono text-sm text-forest">{{ $certificate->certificate_number }}</p>
                    <p class="mt-1 text-sm text-muted">{{ $certificate->planter->name }} · {{ $certificate->planter->identification_number }}</p>
                </div>
                <img src="{{ route('admin.certificates.qr', $certificate) }}" alt="Certificate QR" class="h-28 w-28 rounded-xl bg-white p-2 shadow-sm">
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Issued</dt>
                    <dd class="mt-1 text-sm">{{ $certificate->issued_at ? sl_datetime($certificate->issued_at) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Issued by</dt>
                    <dd class="mt-1 text-sm">{{ $certificate->issuer?->name ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Public verify URL</dt>
                    <dd class="mt-1 break-all text-sm">
                        <a href="{{ $certificate->verifyUrl() }}" class="font-semibold text-leaf hover:text-forest" target="_blank" rel="noopener">{{ $certificate->verifyUrl() }}</a>
                    </dd>
                </div>
                @if ($certificate->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Notes</dt>
                        <dd class="mt-1 text-sm">{{ $certificate->notes }}</dd>
                    </div>
                @endif
                @if ($certificate->isRevoked())
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Revoked</dt>
                        <dd class="mt-1 text-sm">{{ $certificate->revoked_at ? sl_datetime($certificate->revoked_at) : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Revoked by</dt>
                        <dd class="mt-1 text-sm">{{ $certificate->revoker?->name ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Revoke reason</dt>
                        <dd class="mt-1 text-sm">{{ $certificate->revoke_reason }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        @if ($canManage && $certificate->isIssued())
            <form method="POST" action="{{ route('admin.certificates.revoke', $certificate) }}" class="card space-y-4 p-5 sm:p-6" onsubmit="return confirm('Revoke this certificate?')">
                @csrf
                <h2 class="font-semibold text-forest-dark">Revoke certificate</h2>
                <div>
                    <label for="revoke_reason" class="label-field">Reason</label>
                    <textarea id="revoke_reason" name="revoke_reason" rows="3" required class="input-field">{{ old('revoke_reason') }}</textarea>
                    @error('revoke_reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn-danger">Revoke</button>
            </form>
        @endif
    </div>
@endsection
