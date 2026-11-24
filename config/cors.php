<?php

/*
 * Cross-Origin Resource Sharing.
 *
 * The mobile application is a native app and does not use CORS. No browser origin is
 * allowed by default, so the API never sends Access-Control-Allow-Origin unless an
 * origin is listed in CORS_ALLOWED_ORIGINS (comma-separated).
 */

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')),
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
