<?php

namespace A17\TwillFirewall\Services;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use A17\TwillFirewall\Services\Middleware;
use A17\TwillFirewall\Repositories\TwillFirewallRepository;
use A17\TwillFirewall\Models\TwillFirewall as TwillFirewallModel;

class TwillFirewall
{
    use Middleware;

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
        if (filled($this->enabled)) {
            return $this->enabled;
        }

        return $this->enabled =
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

    public function strategy(bool $force = false): string|null
    {
        return $this->get('keys.strategy', 'strategy', $force);
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

        return $this->current->getAttributes()[$key];
    }

    public function hasDotEnv(): bool
    {
        return filled($this->config('keys.allow') ?? null) || filled($this->config('keys.block') ?? null);
    }

    public function isConfigured(): bool
    {
        if (filled($this->isConfigured)) {
            return $this->isConfigured;
        }

        if ($this->hasDotEnv()) {
            return true;
        }

        return $this->isConfigured =
                ($this->isAllowStrategy() && filled($this->allow(true))) ||
                ($this->isBlockStrategy() && filled($this->block(true)));
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

    public function isBlockStrategy()
    {
        return $this->strategy(true) === 'block';
    }

    public function isAllowStrategy()
    {
        return $this->strategy(true) === 'allow';
    }
}
