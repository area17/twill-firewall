<?php

namespace A17\TwillFirewall\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use PragmaRX\Firewall\Firewall;
use PragmaRX\Firewall\Filters\Whitelist;
use Illuminate\Support\Facades\RateLimiter;
use A17\TwillFirewall\Support\Facades\TwillFirewall;
use Symfony\Component\HttpFoundation\IpUtils as SymphonIpUtils;

trait Middleware
{
    use IpUtils;

    public function middleware(Request $request): mixed
    {
        if (!$this->enabled()) {
            return null;
        }

        if ($this->loggedInWithAuthGuard()) {
            return null;
        }

        $response = $this->shouldAllowRequest($request, [
            'strategy' => $this->strategy(),
            'allow' => $this->allow(),
            'block' => $this->block(),
            'guards' => $this->getAuthGuards(),
            'redirect_to' => $this->redirectTo(),
        ]);

        if ($response === 'allow') {
            $response = $this->blockAttackAttemps($response);
        }

        if ($response === false || $response === 'block') {
            return $this->makeBlockResponse();
        }

        return null;
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

    /**
     * @return string
     */
    public function rateLimitingKey(): string
    {
        return 'firewall:' . $this->getIpAddress();
    }

    public function shouldAllowRequest(Request $request, array $config)
    {
        if ($this->routeShouldBeIgnored($request, $config)) {
            return 'allow';
        }

        if ($config['strategy'] === 'allow') {
            return $this->isMissingFromAllowList($config['allow']) ? 'block' : 'allow';
        }

        if ($config['strategy'] === 'block') {
            return $this->isPresentOnBlockList($config['block']) ? 'block' : 'allow';
        }

        return 'allow';
    }

    public function routeShouldBeIgnored(Request $request): bool
    {
        foreach ($this->config('routes.ignore.paths') as $path) {
            if (Str::startsWith($path, '/')) {
                $path = Str::after($path, '/');
            }

            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    public function isMissingFromAllowList(array|string $ipAddresses)
    {
        return !$this->isPresentOnList($ipAddresses);
    }

    public function isPresentOnBlockList(array|string $ipAddresses)
    {
        return $this->isPresentOnList($ipAddresses);
    }

    public function isPresentOnList(array|string $ipAddresses)
    {
        if (is_string($ipAddresses)) {
            $ipAddresses = explode("\n", $ipAddresses);
        }

        return SymphonIpUtils::checkIp($this->getIpAddress(), $ipAddresses);
    }

    public function makeBlockResponse(): Response|RedirectResponse
    {
        $responseConfig = $this->config('responses.'.$this->strategy());

        if (filled($redirectTo = $this->redirectTo())) {
            $responseConfig['redirect_to'] = $redirectTo;
        }

        return (new Responder())->respond($responseConfig);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function blockAttackAttemps(): string
    {
        if ($this->strategy() === 'allow' || !$this->blockAttacks()) {
            return 'allow';
        }

        $rateLimitingKey = $this->rateLimitingKey();

        $response = RateLimiter::attempt(
            $rateLimitingKey,
            $this->config('attacks.max-per-minute', 30),
            fn() => 'allow',
        );

        if ($response !== 'allow') {
            $this->addIpAddressToBlockList($this->getIpAddress());
        }

        return $response === 'allow' ? 'allow' : 'block';
    }

    public function addIpAddressToBlockList($ipAddress)
    {
        $domain = $this->getCurrent();

        $ipAddresses = explode("\n", $domain->block);

        $ipAddresses[] = $ipAddress;

        $domain->block = implode("\n", $ipAddresses);

        $domain->save();
    }
}
