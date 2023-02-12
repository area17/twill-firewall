<?php

namespace A17\TwillFirewall\Services;

use Illuminate\Support\Str;
use A17\TwillFirewall\Services\TwillFirewall;
use Illuminate\Support\Facades\Cache as IlluminateCache;

trait Cache
{
    protected $CACHE_ALL_KEYS = 'twill-firewall.all-keys'; // constant

    public function cacheGet($key, $default = null): mixed
    {
        return IlluminateCache::get($this->makeCacheKey($key), $default);
    }

    public function cachePut($key, $value): void
    {
        $key = $this->makeCacheKey($key);

        IlluminateCache::put($key, $value, $this->cacheExpiration());

        $this->cacheCurrentKey($key);
    }

    public function makeCacheKey($key): string
    {
        return sprintf('twill-firewall.%s.%s', Str::slug($this->getDomain()), Str::slug($key));
    }

    public function cacheExpiration(): int
    {
        return $this->config('cache.ttl', 60 * 60 * 24); // seconds
    }

    public function flushCache(): void
    {
        $keys = IlluminateCache::get($this->CACHE_ALL_KEYS, []);

        foreach ($keys as $key) {
            IlluminateCache::forget($key);
        }
    }

    public function cacheCurrentKey(string $key): void
    {
        $keys = IlluminateCache::get($this->CACHE_ALL_KEYS, []);

        $keys[$key] = $key;

        IlluminateCache::put($this->CACHE_ALL_KEYS, $keys, $this->cacheExpiration());
    }
}
