<?php

namespace A17\TwillFirewall\Models;

use A17\Twill\Models\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\TwillFirewall\Services\Helpers;
use Illuminate\Database\Eloquent\Relations\HasMany;
use A17\TwillFirewall\Models\Behaviors\Encrypt;
use A17\TwillFirewall\Support\Facades\TwillFirewall as TwillFirewallFacade;

/**
 * @property string|null $domain
 */
class TwillFirewall extends Model
{
    use HasRevisions;
    use Encrypt;

    protected $table = 'twill_firewall';

    protected $fillable = ['published', 'domain', 'allow', 'block', 'redirect_to', 'allow_laravel_login', 'allow_twill_login'];

    protected $appends = ['domain_string', 'status', 'from_dot_env'];

    public function revisions(): HasMany
    {
        return $this->hasMany($this->getRevisionModel(), 'twill_firewall_id')->orderBy('created_at', 'desc');
    }

    public function getConfiguredAttribute(): bool
    {
        return filled($this->allow) || filled($this->block);
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
}
