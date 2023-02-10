<?php

namespace A17\TwillFirewall\Http;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use A17\Firewall\Firewall;
use Illuminate\Http\RedirectResponse;
use A17\TwillFirewall\Support\Facades\TwillFirewall;

class Middleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = TwillFirewall::middleware($request);

        if ($response !== null) {
            return $response;
        }

        return $next($request);
    }
}
