<?php

namespace A17\TwillFirewall\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\TwillFirewall\Models\TwillFirewall;

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
}
