<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Honeypot field
    |--------------------------------------------------------------------------
    |
    | Hidden field name bots often fill in. Must stay empty for real users.
    |
    */

    'honeypot_field' => env('REGISTRATION_HONEYPOT_FIELD', 'company_website'),

    /*
    |--------------------------------------------------------------------------
    | Minimum time on form (seconds)
    |--------------------------------------------------------------------------
    */

    'min_seconds' => (int) env('REGISTRATION_MIN_SECONDS', 8),

    /*
    |--------------------------------------------------------------------------
    | Maximum time on form (seconds)
    |--------------------------------------------------------------------------
    */

    'max_seconds' => (int) env('REGISTRATION_MAX_SECONDS', 7200),

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v2 (optional)
    |--------------------------------------------------------------------------
    |
    | Leave keys empty to disable. Create keys at:
    | https://www.google.com/recaptcha/admin
    |
    */

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

];
