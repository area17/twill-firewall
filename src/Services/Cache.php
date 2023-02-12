<?php

namespace A17\TwillFirewall\Services;

use Illuminate\Support\Str;
use A17\TwillFirewall\Services\TwillFirewall;
use Illuminate\Support\Facades\Cache as IlluminateCache;

trait Cache
{
    protected string $CACHE_ALL_KEYS = 'twill-firewall.all-keys'; // constant

    public function cacheGet(string $key, mixed $default = null): mixed
    {
        return IlluminateCache::get($this->makeCacheKey($key), $default);
    }

    public function cachePut(string $key, mixed $value): void
    {
        $key = $this->makeCacheKey($key);

        IlluminateCache::put($key, $value, $this->cacheExpiration());

        $this->cacheCurrentKey($key);
    }

    public function makeCacheKey(string $key): string
    {
        return sprintf('twill-firewall.%s.%s', Str::slug($this->getDomain() ?? 'no-domain'), Str::slug($key));
    }

    public function cacheExpiration(): int
    {
        return $this->config('cache.ttl', 60 * 60 * 24); // seconds
    }

    public function flushCache(): void
    {
        $keys = IlluminateCache::get($this->CACHE_ALL_KEYS, []);

        /* @phpstan-ignore-next-line */
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
