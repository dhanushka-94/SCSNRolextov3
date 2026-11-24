<footer {{ $attributes->merge(['class' => 'text-center text-xs leading-5 text-muted']) }}>
    <x-governing-logos class="mb-4" size="sm" with-system show-names name-layout="below" names="full" />

    <p class="mt-4">
        &copy; {{ now()->year }} {{ config('app.copyright') }}. All rights reserved.
        <span class="mx-1.5 text-tan">·</span>
        {{ config('app.short_name') }} v{{ config('app.version') }}
    </p>
    <p class="mt-1">
        <a href="{{ route('legal.copyright') }}" class="font-semibold text-leaf hover:text-forest">Copyright and Intellectual Property Notice</a>
    </p>
</footer>
