<?php

return [
    'enabled' => env('TWILL_HTTP_BASIC_AUTH_ENABLED', false),

    'rate-limiting' => [
        'attemps-per-minute' => env('TWILL_HTTP_BASIC_AUTH_RATE_LIMITING_ATTEMPTS', 500),
    ],

    'keys' => [
        'username' => env('TWILL_HTTP_BASIC_AUTH_USERNAME'),
        'password' => env('TWILL_HTTP_BASIC_AUTH_PASSWORD'),
    ],

    'inputs' => [
        'email' => ['type' => 'text'],
        'password' => ['type' => 'password'],
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
            'enabled' => env('TWILL_HTTP_BASIC_AUTH_TWILL_DATABASE_LOGIN_ENABLED', false),

            'username-column' => 'email',

            'guard' => 'twill_users',
        ],

        'laravel' => [
            'enabled' => env('TWILL_HTTP_BASIC_AUTH_LARAVEL_DATABASE_LOGIN_ENABLED', false),

            'username-column' => 'email',

            'guard' => 'web',
        ],
    ],
];
