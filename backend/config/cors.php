<?php

$frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        $frontendUrl,
        'https://outstanding-luck-production-1bba.up.railway.app',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*', 'Authorization', 'Content-Type', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
