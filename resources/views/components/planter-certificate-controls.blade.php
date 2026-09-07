@props(['planter'])

@php
    $user = auth('web')->user();
    $canManage = $user?->canManageCertificates() === true;
    $certificate = $planter->relationLoaded('currentCertificate')
        ? $planter->currentCertificate
        : $planter->currentCertificate()->first();
    $latest = $planter->relationLoaded('latestCertificate')
        ? $planter->latestCertificate
        : $planter->latestCertificate()->first();
@endphp

<section class="space-y-4 rounded-2xl border border-sand bg-cream/60 p-5 sm:p-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-forest-dark">Certificate</h3>
            <p class="mt-1 text-sm text-muted">Issue after Final Audit is passed or conditional.</p>
        </div>
        <a href="{{ route('admin.certificates.issued') }}" class="text-sm font-semibold text-leaf hover:text-forest">Issued certificates</a>
    </div>

    @error('certificate')
        <p class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ $message }}</p>
    @enderror

    @if ($certificate)
        <div class="rounded-xl border border-sand bg-paper p-4">
            <p class="font-mono text-xs font-semibold text-forest">{{ $certificate->certificate_number }}</p>
            <p class="mt-2 font-semibold text-forest-dark">{{ $certificate->statusLabel() }} · {{ $certificate->outcomeLabel() }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.certificates.show', $certificate) }}" class="btn-secondary">Open</a>
                <a href="{{ route('admin.certificates.print', $certificate) }}" class="btn-primary" target="_blank">Print</a>
            </div>
        </div>
    @elseif ($latest?->isRevoked())
        <div class="rounded-xl border border-sand bg-paper p-4">
            <p class="text-sm text-muted">Last certificate was revoked.</p>
            <p class="mt-1 font-mono text-xs text-forest">{{ $latest->certificate_number }}</p>
            <a href="{{ route('admin.certificates.show', $latest) }}" class="mt-3 inline-flex text-sm font-semibold text-leaf hover:text-forest">View revoked certificate</a>
        </div>
    @else
        <p class="text-sm text-muted">No certificate issued yet.</p>
    @endif

    @if ($canManage && $planter->canIssueCertificate())
        <form method="POST" action="{{ route('admin.planters.certificates.issue', $planter) }}" class="space-y-3 rounded-xl border border-sand bg-paper p-4">
            @csrf
            <label for="certificate_notes" class="label-field">Issue notes (optional)</label>
            <textarea id="certificate_notes" name="notes" rows="2" class="input-field">{{ old('notes') }}</textarea>
            <button type="submit" class="btn-primary">Issue certificate</button>
        </form>
    @elseif ($canManage && ! $certificate)
        <p class="text-sm text-muted">Final Audit must be passed or conditional before issuing.</p>
    @endif
</section>
