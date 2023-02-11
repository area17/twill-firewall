<?php

namespace A17\TwillFirewall\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use A17\TwillFirewall\Support\Facades\TwillFirewall;

trait Middleware
{
    public function middleware(Request $request): mixed
    {
        if (!$this->enabled()) {
            return null;
        }

        $checkAuth = fn() => $this->checkAuth($request);

        $rateLimitingKey = 'firewall:' . $this->readFromDatabase('allow');

        $response = RateLimiter::attempt(
            $rateLimitingKey,
            $perMinute = $this->config('rate-limiting.attemps-per-minute', 5),
            $checkAuth,
        );

        if (RateLimiter::tooManyAttempts($rateLimitingKey, $perMinute)) {
            abort(429, 'Too many attempts. Please wait one minute and try again.');
        }

        if ($response === null) {
            RateLimiter::clear($rateLimitingKey);
        }

        return $response === true ? null : $response;
    }

    public function checkAuth(Request $request): mixed
    {
        if ($this->loggedInWithAuthGuard()) {
            return true;
        }

        return TwillFirewall::checkAuth($request, [
            'allow' => $this->allow(),
            'block' => $this->block(),
            'guards' => $this->getAuthGuards(),
            'routes' => $this->config('routes'),
        ]);
    }

    public function loggedInWithAuthGuard(): bool
    {
        foreach ($this->getAuthGuards() as $guard) {
            if (auth($guard)->check()) {
                return true;
            }
        }

        return false;
    }

    public function getAuthGuards(): array
    {
        $guards = [];

        foreach ($this->config('database-login', []) as $name => $guard) {
            $enabled = $this->hasDotEnv() ? $guard['enabled'] ?? false : $this->readFromDatabase("allow_{$name}_login");

            if ($enabled) {
                $guards[] = $guard['guard'];
            }
        }

        return $guards;
    }
}
