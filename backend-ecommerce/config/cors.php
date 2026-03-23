<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | This config controls which cross-origin requests are allowed.
    | For local development with Vite, set CORS_ALLOWED_ORIGINS in .env.
    |
    */

    'paths' => ['api/*', 'oauth/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Keep this false for Passport (Bearer tokens) unless you intentionally use cookies.
    'supports_credentials' => false,
];
