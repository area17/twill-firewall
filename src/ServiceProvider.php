<?php

namespace A17\TwillFirewall;

use Illuminate\Support\Str;
use A17\Twill\Facades\TwillCapsules;
use Illuminate\Contracts\Http\Kernel;
use A17\TwillFirewall\Http\Middleware;
use A17\TwillFirewall\Services\Helpers;
use A17\Twill\TwillPackageServiceProvider;
use A17\TwillFirewall\Services\TwillFirewall;

class ServiceProvider extends TwillPackageServiceProvider
{
    /** @var bool $autoRegisterCapsules */
    protected $autoRegisterCapsules = false;

    protected $packageName = 'twill-firewall';

    protected $configSections = [
        'attacks',
        'cache',
        'database-login',
        'inputs',
        'keys',
        'middleware',
        'responses',
        'routes',
    ];

    public function boot(): void
    {
        if (!$this->registerConfig()) {
            return;
        }

        $this->registerViews();

        $this->configureMiddeleware();

        $this->registerThisCapsule();

        parent::boot();
    }

    protected function registerThisCapsule(): void
    {
        $namespace = $this->getCapsuleNamespace();

        TwillCapsules::registerPackageCapsule(
            Str::afterLast($namespace, '\\'),
            $namespace,
            $this->getPackageDirectory() . '/src',
        );

        app()->singleton(TwillFirewall::class, fn() => new TwillFirewall());
    }

    public function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'twillFirewalls');
    }

    public function registerConfig(): bool
    {
        $path = __DIR__ . "/config/{$this->packageName}.php";

        $this->mergeConfigFrom($path, $this->packageName);

        foreach ($this->configSections as $section) {
            $path = __DIR__ . "/config/{$section}.php";

            $this->mergeConfigFrom($path, "{$this->packageName}.{$section}");
        }

        $this->publishes([
            $path => config_path("{$this->packageName}.php"),
        ]);

        return config('twill-firewall.enabled');
    }

    public function configureMiddeleware(): void
    {
        if (config('twill-firewall.middleware.automatic')) {
            /**
             * @phpstan-ignore-next-line
             * @var \Illuminate\Foundation\Http\Kernel $kernel
             */
            $kernel = $this->app[Kernel::class];

            $method = config('twill-firewall.middleware.method');

            $method = $method === 'append' ? 'appendMiddlewareToGroup' : 'prependMiddlewareToGroup';

            foreach (config('twill-firewall.middleware.groups', []) as $group) {
                $kernel->$method($group, config('twill-firewall.middleware.class'));
            }
        }
    }
}
