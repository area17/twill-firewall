<?php

namespace A17\TwillFirewall;

use Illuminate\Support\Str;
use A17\Twill\Facades\TwillCapsules;
use Illuminate\Contracts\Http\Kernel;
use A17\Twill\TwillPackageServiceProvider;
use A17\TwillFirewall\Http\Middleware;
use A17\TwillFirewall\Services\Helpers;
use A17\TwillFirewall\Services\TwillFirewall;

class ServiceProvider extends TwillPackageServiceProvider
{
    /** @var bool $autoRegisterCapsules */
    protected $autoRegisterCapsules = false;

    public function boot(): void
    {
        $this->registerThisCapsule();

        $this->registerViews();

        $this->registerConfig();

        $this->configureMiddeleware();

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
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'twill-firewall');
    }

    public function registerConfig(): void
    {
        $package = 'twill-firewall';

        $path = __DIR__ . "/config/{$package}.php";

        $this->mergeConfigFrom($path, $package);

        $this->publishes([
            $path => config_path("{$package}.php"),
        ]);
    }

    public function configureMiddeleware(): void
    {
        if (config('twill-firewall.middleware.automatic')) {
            /**
             * @phpstan-ignore-next-line
             * @var \Illuminate\Foundation\Http\Kernel $kernel
             */
            $kernel = $this->app[Kernel::class];

            foreach (config('twill-firewall.middleware.groups', []) as $group) {
                $kernel->appendMiddlewareToGroup($group, config('twill-firewall.middleware.class'));
            }
        }
    }
}
