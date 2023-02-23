<?php

namespace A17\TwillFirewall\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use A17\Twill\Http\Controllers\Admin\ModuleController;
use A17\TwillFirewall\Repositories\TwillFirewallRepository;
use A17\TwillFirewall\Support\Facades\TwillFirewall as TwillFirewallFacade;

class TwillFirewallController extends ModuleController
{
    protected $moduleName = 'twillFirewalls';

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

        'strategy' => [
            'title' => 'Strategy',
            'field' => 'strategy',
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

    public function index(int|null $parentModuleId = null): array|View|RedirectResponse|JsonResponse
    {
        app(TwillFirewallRepository::class)->generateDomains();

        $this->setIndexOptions();

        return parent::index($parentModuleId);
    }

    protected function getViewPrefix(): string|null
    {
        return 'twillFirewalls::twill';
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
