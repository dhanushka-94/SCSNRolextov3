<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify certificate · {{ config('app.short_name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-cream text-ink">
    <main class="mx-auto max-w-lg px-4 py-10">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan">{{ config('app.short_name') }}</p>
            <h1 class="mt-2 text-2xl font-semibold text-forest-dark">Verify a certificate</h1>
            <p class="mt-2 text-sm text-muted">Enter a certificate number (e.g. SCSNR/CERT/2026/00001) or SCSNR ID.</p>
        </div>

        <form method="POST" action="{{ route('verify.lookup') }}" class="card mt-8 space-y-4 p-5 sm:p-6">
            @csrf
            <div>
                <label for="ref" class="label-field">Reference number</label>
                <input id="ref" name="ref" type="text" value="{{ old('ref', $ref) }}" required class="input-field font-mono" placeholder="SCSNR/CERT/2026/00001" autofocus>
                @error('ref')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
                @if ($error)
                    <p class="mt-2 text-sm text-red-700">{{ $error }}</p>
                @endif
            </div>
            <button type="submit" class="btn-primary w-full">Verify</button>
        </form>

        <p class="mt-6 text-center text-xs text-muted">
            You can also open a QR verify link directly.
            <a href="{{ route('partner.home') }}" class="font-semibold text-leaf hover:text-forest">Back to portal</a>
        </p>
    </main>
</body>
</html>
