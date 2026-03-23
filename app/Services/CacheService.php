<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function remember(string $key, int $ttl, \Closure $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function put(string $key, $value, int $ttl): void
    {
        Cache::put($key, $value, $ttl);
    }

    public function get(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    public function invalidate(string $pattern): void
    {
        // For Redis, using pattern matching
        if (config('cache.default') === 'redis') {
            $keys = Cache::getStore()->connection()->keys($pattern);
            foreach ($keys as $key) {
                Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
            }
        }
    }
}