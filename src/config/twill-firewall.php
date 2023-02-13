<?php

return [
    'enabled' => env('TWILL_FIREWALL_ENABLED', false),

    'keys' => [
        'allow' => env('TWILL_FIREWALL_ALLOW'),
        'block' => env('TWILL_FIREWALL_BLOCK'),
    ],

    'inputs' => [
        'allow' => [
            'type' => 'textarea',
            'rows' => 10,
        ],

        'block' => [
            'type' => 'textarea',
            'rows' => 10,
        ],
    ],

    'middleware' => [
        'automatic' => true, // Do it yourself to optimize the middleware stack for speed

        'method' => 'append', // 'prepend' (faster if you don't need session login) or 'append'

        'groups' => ['web'],

        'class' => \A17\TwillFirewall\Http\Middleware::class,
    ],

    'routes' => [
        'ignore' => [
            'paths' => ['/admin/*', '/api/v1/*'],
        ],
    ],

    /**
     * Database login
     *
     * This feature is intended will prevent logged in users from being blocked by the firewall or detected as attacks.
     */
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

    'attacks' => [
        'block' => env('TWILL_BLOCK_ATTACKS_ENABLED', false),
        'add-blocked-to-list' => env('TWILL_ADD_BLOCKED_TO_BLOCK_LIST', false),
        'max-per-minute' => env('TWILL_BLOCK_ATTACKS_RATE_PER_MINUTE', 30),
        'max-automatic-ip-addresses' => env('TWILL_BLOCK_ATTACKS_MAX_IPS', 1000),
    ],

    'responses' => [
        'allow' => [
            'code' => 403, // 200 = log && notify, but keep pages rendering

            'message' => null,

            'view' => null,

            'redirect_to' => null,

            'should_abort' => false, // return abort() instead of Response::make() - disabled by default
        ],

        'block' => [
            'code' => 403, // 200 = log && notify, but keep pages rendering

            'message' => null,

            'view' => null,

            'redirect_to' => null,

            'should_abort' => false, // return abort() instead of Response::make() - disabled by default
        ],
    ],

    'cache' => [
        'enabled' => env('TWILL_FIREWALL_CACHE_ENABLED', true),

        'ttl' => env('TWILL_FIREWALL_CACHE_TTL', 60 * 60 * 24), // 24 hours
    ],
];
