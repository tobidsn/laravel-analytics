<?php

namespace Tobidsn\LaravelAnalytics\Services;

use Illuminate\Contracts\Cache\Repository;

class AnalyticsCacheService
{
    public function __construct(
        private readonly Repository $cache,
        private readonly int $defaultTtl,
        private readonly string $prefix
    ) {}

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $cacheKey = $this->prefix.$key;

        return $this->cache->remember($cacheKey, $ttl ?? $this->defaultTtl, $callback);
    }

    public function forget(string $key): bool
    {
        $cacheKey = $this->prefix.$key;

        return $this->cache->forget($cacheKey);
    }

    public function flush(): bool
    {
        return $this->cache->flush();
    }

    public function generateKey(string $method, array $params = []): string
    {
        $paramString = md5(serialize($params));

        return "{$method}:{$paramString}";
    }
}
