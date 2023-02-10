<?php

namespace A17\TwillFirewall\Http\Requests;

use A17\Twill\Http\Requests\Admin\Request;

class TwillFirewallRequest extends Request
{
    public function rulesForCreate(): array
    {
        return [];
    }

    public function rulesForUpdate(): array
    {
        return [];
    }
}
