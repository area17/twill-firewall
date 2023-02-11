<?php

namespace A17\TwillFirewall\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\TwillFirewall\Models\TwillFirewall;
use A17\TwillFirewall\Support\Facades\TwillFirewall as TwillFirewallFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * @method \Illuminate\Database\Eloquent\Builder published()
 */
class TwillFirewallRepository extends ModuleRepository
{
    use HandleRevisions;

    public function __construct(TwillFirewall $model)
    {
        $this->model = $model;
    }

    public function generateDomains(): void
    {
        if (DB::table('twill_firewall')->count() !== 0) {
            return;
        }

        $appDomain = TwillFirewallFacade::getDomain(config('app.url'));

        $currentDomain = TwillFirewallFacade::getDomain(URL::current());

        /** @phpstan-ignore-next-line  */
        app(TwillFirewallRepository::class)->create([
            'domain' => '*',
            'published' => false,
        ]);

        if (filled($currentDomain)) {
            /** @phpstan-ignore-next-line  */
            app(TwillFirewallRepository::class)->create([
                'domain' => $currentDomain,
                'published' => false,
            ]);
        }

        if (filled($appDomain) && $appDomain !== $currentDomain) {
            /** @phpstan-ignore-next-line  */
            app(TwillFirewallRepository::class)->create([
                'domain' => $appDomain,
                'published' => false,
            ]);
        }
    }
}
