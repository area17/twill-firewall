<?php

namespace A17\TwillFirewall;

use Illuminate\Support\Str;
use A17\Twill\Facades\TwillCapsules;
use Illuminate\Contracts\Http\Kernel;
use A17\TwillFirewall\Http\Middleware;
use A17\TwillFirewall\Services\Helpers;
use A17\Twill\TwillPackageServiceProvider;
use A17\TwillFirewall\Services\TwillFirewall;
use A17\TwillFirewall\Console\Commands\ConfigMergeSection;
use A17\TwillFirewall\Console\Commands\ConfigListSections;

class ServiceProvider extends TwillPackageServiceProvider
{
    /** @var bool $autoRegisterCapsules */
    protected $autoRegisterCapsules = false;

    protected string $packageName = 'twill-firewall';

    protected array $configSections = [
        'attacks',
        'cache',
        'database-login',
        'inputs',
        'keys',
        'middleware',
        'responses',
        'routes',
        'package',
    ];

    protected bool $enabled = false;

    protected string|null $mainConfigPath = null;

    public function register(): void
    {
        $this->registerConfig();

        if (!$this->enabled) {
            return;
        }

        $this->registerThisCapsule();
    }

    public function boot(): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->bootConfig();

        $this->bootMiddeleware();

        $this->bootViews();

        $this->bootCommands();

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

    public function bootViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'twillFirewalls');
    }

    public function registerConfig(): void
    {
        $this->registerMainConfig();

        $this->registerConfigSections();

        $this->enabled = config('twill-firewall.enabled');
    }

    public function bootMiddeleware(): void
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

    public function bootCommands(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            ConfigMergeSection::class,
            ConfigListSections::class,
        ]);
    }

    public function bootConfig(): void
    {
        $this->publishes([
            $this->mainConfigPath => config_path("{$this->packageName}.php"),
        ]);
    }

    public function registerMainConfig(): void
    {
        $this->mainConfigPath = __DIR__ . "/config/{$this->packageName}.php";

        $this->mergeConfigFrom($this->mainConfigPath, $this->packageName);
    }

    public function registerConfigSections(): void
    {
        foreach ($this->configSections as $section) {
            $this->mergeConfigFrom(
                __DIR__ . "/config/{$section}.php",
                "{$this->packageName}.{$section}"
            );
        }

        config(['twill-firewall.package.name' => $this->packageName]);

        config(['twill-firewall.package.sections' => $this->configSections]);
    }
}
