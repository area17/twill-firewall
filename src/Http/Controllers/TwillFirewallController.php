<?php

namespace A17\TwillFirewall\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use A17\TwillFirewall\Models\TwillFirewall;
use A17\Twill\Http\Controllers\Admin\ModuleController;
use A17\TwillFirewall\Repositories\TwillFirewallRepository;
use A17\TwillFirewall\Support\Facades\TwillFirewall as TwillFirewallFacade;

class TwillFirewallController extends ModuleController
{
    protected $moduleName = 'twillFirewall';

    protected $titleColumnKey = 'domain_string';

    protected $titleFormKey = 'domain';

    protected $defaultOrders = ['domain' => 'asc'];

    protected $indexColumns = [
        'domain_string' => [
            'title' => 'Domain',
            'field' => 'domain_string',
        ],

        'status' => [
            'title' => 'Status',
            'field' => 'status',
        ],

        'allow_laravel_login' => [
            'title' => 'Laravel login',
            'field' => 'allow_laravel_login',
        ],

        'allow_twill_login' => [
            'title' => 'Twill login',
            'field' => 'allow_twill_login',
        ],

        'from_dot_env' => [
            'title' => 'From .env',
            'field' => 'from_dot_env',
        ],
    ];

    /**
     * @param int|null $parentModuleId
     * @return array|\Illuminate\View\View|RedirectResponse
     */
    public function index($parentModuleId = null)
    {
        app(TwillFirewallRepository::class)->generateDomains();

        $this->setIndexOptions();

        return parent::index($parentModuleId = null);
    }

    protected function getViewPrefix(): string|null
    {
        return 'twill-firewall::admin';
    }

    public function setIndexOptions(): void
    {
        $this->indexOptions = ['create' => !TwillFirewallFacade::allDomainsEnabled()];
    }

    /**
     * @param array $scopes
     * @param bool $forcePagination
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getIndexItems($scopes = [], $forcePagination = false)
    {
        if (TwillFirewallFacade::allDomainsEnabled()) {
            $scopes['domain'] = '*';
        }

        return parent::getIndexItems($scopes, $forcePagination);
    }
}
