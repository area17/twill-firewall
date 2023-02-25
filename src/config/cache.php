<?php

return [
    'enabled' => env('TWILL_FIREWALL_CACHE_ENABLED', true),

    'ttl' => env('TWILL_FIREWALL_CACHE_TTL', 60 * 60 * 24), // 24 hours
];
