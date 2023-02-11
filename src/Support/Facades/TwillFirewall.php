<?php

namespace A17\TwillFirewall\Support\Facades;

use Illuminate\Support\Facades\Facade;
use A17\TwillFirewall\Services\TwillFirewall as TwillFirewallService;

class TwillFirewall extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TwillFirewallService::class;
    }
}
