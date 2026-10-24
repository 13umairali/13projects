<?php

return [

    'paths' => [
        'api/*',
        'oauth/*',
        'sanctum/csrf-cookie',
        'storage/*',
    ],

    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization', 'X-CSRF-TOKEN'],
    'max_age' => 0,
    'supports_credentials' => true,
];
