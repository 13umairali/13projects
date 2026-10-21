<?php
return [
    'default' => env('BROADCAST_CONNECTION', 'null'),
    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'), 'secret' => env('PUSHER_APP_SECRET'), 'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'host' => env('PUSHER_HOST', 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com'),
                'port' => env('PUSHER_PORT', 443), 'scheme' => env('PUSHER_SCHEME', 'https'),
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
            'client_options' => ['connect_timeout' => 2, 'timeout' => 3],
        ],
        // Self-hosted websockets (laravel/reverb, Pusher protocol). This is what makes
        // call / meeting signalling instant: offer / answer / ICE candidates are pushed
        // to the other device the moment they are sent, instead of waiting for the next
        // REST poll. Point the clients at it with PUSHER_WS_HOST / PUSHER_WS_PORT /
        // PUSHER_WS_SCHEME (see services.realtime and App\Support\ClientRealtime).
        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
            ],
            'client_options' => ['connect_timeout' => 2, 'timeout' => 3],
        ],
        'log' => ['driver' => 'log'],
        'null' => ['driver' => 'null'],
    ],
];
