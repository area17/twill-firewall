<?php

/**
 * Database login
 *
 * This feature is intended will prevent logged in users from being blocked by the firewall or detected as attacks.
 */

return [
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
];
