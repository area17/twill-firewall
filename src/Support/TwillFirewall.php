<?php

namespace A17\TwillFirewall\Support;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use A17\Firewall\Middleware;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use A17\Firewall\Firewall;
use Illuminate\Support\Facades\RateLimiter;
use A17\TwillFirewall\Models\Behaviors\Encrypt;
use A17\TwillFirewall\Repositories\TwillFirewallRepository;
use A17\TwillFirewall\Models\TwillFirewall as TwillFirewallModel;

class TwillFirewall
{
    use Encrypt;

    public const DEFAULT_ERROR_MESSAGE = 'Invisible captcha failed.';

    protected array|null $config = null;

    protected bool|null $isConfigured = null;

    protected bool|null $enabled = null;

    protected Response|null $firewallResponse = null;

    protected TwillFirewallModel|null $current = null;

    public function config(string|null $key = null, mixed $default = null): mixed
    {
        $this->config ??= filled($this->config) ? $this->config : (array) config('twill-firewall');

        if (blank($key)) {
            return $this->config;
        }

        return Arr::get((array) $this->config, $key) ?? $default;
    }

    public function enabled(): bool
    {
        return $this->enabled ??
            ($this->hasDotEnv() ? $this->config('enabled') : true) &&
                $this->isConfigured() &&
                (!$this->hasDotEnv() || $this->readFromDatabase('published'));
    }

    public function allow(bool $force = false): string|null
    {
        return $this->get('keys.allow', 'allow', $force);
    }

    public function block(bool $force = false): string|null
    {
        return $this->get('keys.block', 'block', $force);
    }

    public function redirectTo(bool $force = false): string|null
    {
        return $this->get('keys.redirect_to', 'redirect_to', $force);
    }

    public function published(bool $force = false): string|null
    {
        return $this->get('enabled', 'published', $force);
    }

    public function get(string $configKey, string $databaseColumn, bool $force = false): string|null
    {
        if (!$force && (!$this->isConfigured() || !$this->enabled())) {
            return null;
        }

        return $this->hasDotEnv() ? $this->config($configKey) : $this->readFromDatabase($databaseColumn);
    }

    protected function readFromDatabase(string $key): string|bool|null
    {
        if (blank($this->current)) {
            $domains = app(TwillFirewallRepository::class)
                ->published()
                ->orderBy('domain')
                ->get();

            if ($domains->isEmpty()) {
                return null;
            }

            /** @var TwillFirewallModel|null $domain */
            $domain = $domains->first();

            if ($domain !== null && $domain->domain === '*') {
                $this->current = $domain;
            } else {
                /** @var TwillFirewallModel|null $domain */
                $domain = $domains->firstWhere('domain', $this->getDomain());

                $this->current = $domain;
            }
        }

        if ($this->current === null) {
            return null;
        }

        return $this->decrypt($this->current->getAttributes()[$key]);
    }

    public function hasDotEnv(): bool
    {
        return filled($this->config('keys.allow') ?? null) || filled($this->config('keys.block') ?? null);
    }

    protected function isConfigured(): bool
    {
        return $this->isConfigured ??
            $this->hasDotEnv() || (filled($this->allow(true)) && filled($this->block(true)));
    }

    protected function setConfigured(): void
    {
        $this->isConfigured = $this->isConfigured();
    }

    protected function setEnabled(): void
    {
        $this->enabled = $this->enabled();
    }

    protected function configureViews(): void
    {
        View::addNamespace('firewall', __DIR__ . '/../resources/views');
    }

    public function getDomain(string|null $url = null): string|null
    {
        $url = parse_url($url ?? request()->url());

        return $url['host'] ?? null;
    }

    public function setCurrent(TwillFirewallModel $current): static
    {
        $this->current = $current;

        return $this;
    }

    public function allDomainsEnabled(): bool
    {
        return $this->hasDotEnv() || $this->readFromDatabase('domain') === '*';
    }

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

    public function checkAuth(Request $request): mixed
    {
        if ($this->loggedInWithAuthGuard()) {
            return true;
        }

        return Firewall::checkAuth($request, [
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
}
