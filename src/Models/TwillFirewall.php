<?php

namespace A17\TwillFirewall\Models;

use A17\Twill\Models\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\TwillFirewall\Services\Helpers;
use Illuminate\Database\Eloquent\Relations\HasMany;
use A17\TwillFirewall\Support\Facades\TwillFirewall as TwillFirewallFacade;

/**
 * @property string|null $domain
 * @property bool $published
 * @property string $domain
 * @property string $allow
 * @property string $block
 * @property string $redirect_to
 * @property bool $allow_laravel_login
 * @property bool $allow_twill_login
 * @property string $strategy
 * @property bool $block_attacks
 * @property bool $add_blocked_to_list
 * @property int $max_requests_per_minute
 */
class TwillFirewall extends Model
{
    use HasRevisions;

    protected $table = 'twill_firewall';

    protected $fillable = [
        'published',
        'domain',
        'allow',
        'block',
        'redirect_to',
        'allow_laravel_login',
        'allow_twill_login',
        'strategy',
        'block_attacks',
        'add_blocked_to_list',
        'max_requests_per_minute',
    ];

    protected $appends = ['domain_string', 'status', 'from_dot_env'];

    public function revisions(): HasMany
    {
        return $this->hasMany($this->getRevisionModel(), 'twill_firewall_id')->orderBy('created_at', 'desc');
    }

    public function getConfiguredAttribute(): bool
    {
        return TwillFirewallFacade::hasDotEnv() ||
            ($this->strategy === 'allow' && filled($this->allow)) ||
            ($this->strategy === 'block' && filled($this->block));
    }

    public function getStatusAttribute(): string
    {
        if ($this->published && $this->configured) {
            return 'protected';
        }

        if ($this->domain === '*') {
            return 'disabled';
        }

        return 'unprotected';
    }

    public function getFromDotEnvAttribute(): string
    {
        return TwillFirewallFacade::hasDotEnv() ? 'yes' : 'no';
    }

    public function getDomainStringAttribute(): string|null
    {
        $domain = $this->domain;

        if ($domain === '*') {
            return '* (all domains)';
        }

        return $domain;
    }

    public function save(array $options = [])
    {
        TwillFirewallFacade::flushCache();

        return parent::save($options);
    }
}
