{{-- Spam trap: hidden from users, must stay empty. --}}
<div class="pointer-events-none absolute -left-[9999px] top-auto h-0 w-0 overflow-hidden opacity-0" aria-hidden="true">
    <label for="{{ config('registration.honeypot_field') }}">Company website</label>
    <input
        type="text"
        name="{{ config('registration.honeypot_field') }}"
        id="{{ config('registration.honeypot_field') }}"
        value=""
        tabindex="-1"
        autocomplete="off"
    >
</div>
