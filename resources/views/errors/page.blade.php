<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('app.short_name') }}</title>
    <link rel="icon" href="{{ asset('SPPNR-logo.png') }}">
    <x-app-fonts />
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-cream text-ink">
    <main class="mx-auto flex w-full max-w-lg flex-1 flex-col items-center justify-center px-4 py-12 text-center">
        <img src="{{ asset('SPPNR-logo.png') }}" alt="" class="h-16 w-16 rounded-full bg-white object-contain p-1 shadow-sm">
        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-tan">{{ $code }}</p>
        <h1 class="mt-2 font-display text-3xl text-forest-dark">{{ $title }}</h1>
        <p class="mt-3 text-sm leading-6 text-muted">{{ $message }}</p>
        <a href="{{ route('partner.home') }}" class="btn-primary mt-6">Back to home</a>
    </main>
    <x-app-footer class="px-4 py-8" />
</body>
</html>
