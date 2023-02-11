<?php

namespace A17\TwillFirewall\Services;

use A17\TwillFirewall\Services\TwillFirewall;

class Helpers
{
    public static function load(): void
    {
        require __DIR__ . '/../Support/helpers.php';
    }

    public static function instance(): TwillFirewall
    {
        if (!app()->bound('firewall')) {
            app()->singleton('firewall', fn() => new TwillFirewall());
        }

        return app('firewall');
    }
}
