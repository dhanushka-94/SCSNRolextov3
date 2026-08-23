<footer {{ $attributes->merge(['class' => 'text-center text-xs leading-5 text-muted']) }}>
    <x-governing-logos class="mb-4" size="sm" show-names name-layout="inline" />

    <p class="mt-4">
        &copy; {{ now()->year }} {{ config('app.copyright') }}. All rights reserved.
        <span class="mx-1.5 text-tan">·</span>
        {{ config('app.short_name') }} v{{ config('app.version') }}
    </p>
</footer>
