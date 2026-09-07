<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify · {{ $certificate->certificate_number }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-cream text-ink">
    <main class="mx-auto max-w-lg px-4 py-10">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan">{{ config('app.short_name') }}</p>
            <h1 class="mt-2 text-2xl font-semibold text-forest-dark">Certificate verification</h1>
        </div>

        <article class="card mt-8 overflow-hidden">
            <div class="border-b border-sand px-5 py-5 {{ $certificate->isRevoked() ? 'bg-red-50' : 'bg-cream' }}">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Result</p>
                <p class="mt-1 text-xl font-semibold {{ $certificate->isRevoked() ? 'text-red-800' : 'text-forest-dark' }}">
                    {{ $certificate->publicVerificationLabel() }}
                </p>
                <p class="mt-1 font-mono text-sm text-forest">{{ $certificate->certificate_number }}</p>
            </div>

            <dl class="grid gap-4 p-5">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Planter</dt>
                    <dd class="mt-1 text-sm font-semibold">{{ $planter->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">SCSNR ID</dt>
                    <dd class="mt-1 font-mono text-sm">{{ $planter->identification_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">District / Farm</dt>
                    <dd class="mt-1 text-sm">{{ $planter->district ?: '—' }} · {{ $planter->farm_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Outcome</dt>
                    <dd class="mt-1 text-sm">{{ $certificate->outcomeLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Issued</dt>
                    <dd class="mt-1 text-sm">{{ $certificate->issued_at?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                @if ($certificate->isRevoked())
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Revoked</dt>
                        <dd class="mt-1 text-sm">{{ $certificate->revoked_at?->format('Y-m-d') ?? '—' }}</dd>
                    </div>
                @endif
            </dl>

            <div class="flex justify-center border-t border-sand px-5 py-5">
                <img src="{{ route('verify.qr', $certificate->public_token) }}" alt="Certificate QR" class="h-32 w-32 rounded-xl bg-white p-2 ring-1 ring-sand">
            </div>
        </article>

        <p class="mt-6 text-center text-xs text-muted">
            <a href="{{ route('verify.form') }}" class="font-semibold text-leaf hover:text-forest">Verify by reference number</a>
            ·
            <a href="{{ route('partner.home') }}" class="font-semibold text-leaf hover:text-forest">{{ config('app.short_name') }} partner portal</a>
        </p>
    </main>
</body>
</html>
