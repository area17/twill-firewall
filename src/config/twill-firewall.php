<?php

return [
    'enabled' => env('TWILL_FIREWALL_ENABLED', false),

    'rate-limiting' => [
        'attemps-per-minute' => env('TWILL_FIREWALL_RATE_LIMITING_ATTEMPTS', 500),
    ],

    'keys' => [
        'allow' => env('TWILL_FIREWALL_ALLOW'),
        'block' => env('TWILL_FIREWALL_BLOCK'),
    ],

    'inputs' => [
        'allow' => [
            'type' => 'textarea',
            'rows' => 10
        ],

        'block' => [
            'type' => 'textarea',
            'rows' => 10
        ],
    ],

    'middleware' => [
        'automatic' => true,

        'groups' => ['web'],

        'class' => \A17\TwillFirewall\Http\Middleware::class,
    ],

    'routes' => [
        'ignore' => [
            'paths' => ['/admin/*', '/api/v1/*'],
        ],
    ],

    'database-login' => [
        'twill' => [
            'enabled' => env('TWILL_FIREWALL_TWILL_DATABASE_LOGIN_ENABLED', false),

            'username-column' => 'email',

            'guard' => 'twill_users',
        ],

        'laravel' => [
            'enabled' => env('TWILL_FIREWALL_LARAVEL_DATABASE_LOGIN_ENABLED', false),

            'username-column' => 'email',

            'guard' => 'web',
        ],
    ],
];
