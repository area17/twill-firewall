<?php

return [
    'block' => env('TWILL_BLOCK_ATTACKS_ENABLED', false),
    'add-blocked-to-list' => env('TWILL_ADD_BLOCKED_TO_BLOCK_LIST', false),
    'max-per-minute' => env('TWILL_BLOCK_ATTACKS_RATE_PER_MINUTE', 30),
    'max-automatic-ip-addresses' => env('TWILL_BLOCK_ATTACKS_MAX_IPS', 1000),
];
