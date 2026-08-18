<footer {{ $attributes->merge(['class' => 'text-center text-xs leading-5 text-muted']) }}>
    <div class="mb-2 flex items-center justify-center gap-2">
        <img src="{{ asset('RRISL-logo.png') }}" alt="Rubber Research Institute of Sri Lanka" class="h-8 w-8 rounded-full bg-black object-cover">
        <span class="font-semibold text-bark">Official portal of the Rubber Research Institute of Sri Lanka</span>
    </div>
    <p>&copy; {{ now()->year }} {{ config('app.copyright') }}. All rights reserved.</p>
    <p class="mt-0.5">
        {{ config('app.full_name') }} ({{ config('app.short_name') }})
        <span class="mx-1.5 text-tan">·</span>
        Version {{ config('app.version') }}
    </p>
</footer>
