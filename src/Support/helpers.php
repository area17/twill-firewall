<?php

use A17\TwillFirewall\Services\Helpers;
use A17\TwillFirewall\Support\TwillFirewall;

if (!function_exists('firewall')) {
    function firewall(): TwillFirewall
    {
        return Helpers::instance();
    }
}
