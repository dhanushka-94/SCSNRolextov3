@props(['siteKey' => null])

@if ($siteKey)
    <div class="g-recaptcha" data-sitekey="{{ $siteKey }}"></div>
    @once
        @push('scripts')
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @endpush
    @endonce
@endif
