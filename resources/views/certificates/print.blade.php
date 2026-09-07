<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $certificate->certificate_number }} · {{ config('app.short_name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .certificate-sheet { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body class="bg-cream text-ink">
    <div class="no-print mx-auto flex max-w-4xl justify-end gap-2 px-4 py-4">
        <button type="button" onclick="window.print()" class="btn-primary">Print</button>
        <button type="button" onclick="window.close()" class="btn-secondary">Close</button>
    </div>

    <article class="certificate-sheet mx-auto my-4 max-w-4xl rounded-2xl border border-sand bg-paper p-8 shadow-sm sm:p-12">
        <header class="border-b border-sand pb-6 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan">{{ config('app.short_name') }}</p>
            <h1 class="mt-2 text-2xl font-semibold text-forest-dark sm:text-3xl">Sustainability Certificate</h1>
            <p class="mt-2 text-sm text-muted">{{ config('app.full_name') }}</p>
        </header>

        <div class="mt-8 grid gap-8 sm:grid-cols-[1fr_auto]">
            <div class="space-y-4">
                <p class="text-sm text-muted">This certifies that</p>
                <p class="text-2xl font-semibold text-forest-dark">{{ $certificate->planter->name }}</p>
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">SCSNR ID</dt>
                        <dd class="mt-1 font-mono">{{ $certificate->planter->identification_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Certificate No.</dt>
                        <dd class="mt-1 font-mono">{{ $certificate->certificate_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">District</dt>
                        <dd class="mt-1">{{ $certificate->planter->district ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Farm</dt>
                        <dd class="mt-1">{{ $certificate->planter->farm_name ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Outcome</dt>
                        <dd class="mt-1 font-semibold">{{ $certificate->outcomeLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Issued</dt>
                        <dd class="mt-1">{{ $certificate->issued_at?->format('Y-m-d') ?? '—' }}</dd>
                    </div>
                </dl>
                @if ($certificate->isRevoked())
                    <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                        REVOKED — {{ $certificate->revoked_at?->format('Y-m-d') }}
                    </p>
                @endif
            </div>
            <div class="flex flex-col items-center gap-2">
                <img src="{{ $qrUrl }}" alt="Verification QR" class="h-36 w-36 rounded-xl bg-white p-2 ring-1 ring-sand">
                <p class="max-w-[9rem] text-center text-[10px] text-muted">Scan to verify</p>
            </div>
        </div>

        <footer class="mt-10 border-t border-sand pt-6 text-center text-xs text-muted">
            <p>{{ config('app.powered_by') }}</p>
            <p class="mt-2">{{ config('app.short_name') }} v{{ config('app.version') }}</p>
            <p class="mt-2">Verify at {{ $certificate->verifyUrl() }}</p>
        </footer>
    </article>
</body>
</html>
