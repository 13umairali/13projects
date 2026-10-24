<?php

namespace App\Support;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * The panel's cache of backend API responses.
 *
 * Two layers, both handled here:
 *
 *  1. A per-request memo, so the same endpoint fetched twice while rendering one
 *     page costs one HTTP call — the API-client equivalent of an N+1. This layer
 *     works even when caching is switched off.
 *  2. A shared cache (whatever CACHE_STORE points at) with a TTL from config.
 *
 * Invalidation is by generation counter rather than key bookkeeping: every key
 * embeds the current generation, and any successful write through any service
 * bumps it, which orphans every previously cached read at once. That makes
 * stale-after-CRUD structurally impossible — the cost is a cold cache after each
 * write, which is the right trade for an admin panel.
 *
 * Registered as a singleton, so the memo lives exactly as long as one request.
 */
class ApiCache
{
    /** @var array<string, mixed> */
    private array $memo = [];

    private ?int $generation = null;

    /**
     * TTL in seconds, clamped to the configured window (30 minutes … 1 day).
     * A configured 0 means "no caching at all".
     */
    public function ttl(): int
    {
        $ttl = (int) config('saas.cache.ttl', 1800);

        if ($ttl <= 0) {
            return 0;
        }

        $min = (int) config('saas.cache.min_ttl', 1800);
        $max = (int) config('saas.cache.max_ttl', 86400);

        return max($min, min($ttl, $max));
    }

    public function enabled(): bool
    {
        return (bool) config('saas.cache.enabled', true) && $this->ttl() > 0;
    }

    /**
     * Resolve a value, serving it from the memo, then the cache, then the API.
     *
     * A null resolution (an unreachable backend) is cached briefly rather than
     * not at all, so an outage cannot turn every page view into a fresh timeout.
     */
    public function remember(string $key, callable $resolver, mixed $default = null): mixed
    {
        $cacheKey = $this->key($key);

        if (array_key_exists($cacheKey, $this->memo)) {
            return $this->memo[$cacheKey] ?? $default;
        }

        if (! $this->enabled()) {
            return $this->memo[$cacheKey] = $resolver();
        }

        try {
            if (($cached = Cache::get($cacheKey)) !== null) {
                return $this->memo[$cacheKey] = ($cached['v'] ?? null) ?? $default;
            }

            $value = $resolver();

            Cache::put(
                $cacheKey,
                ['v' => $value],
                now()->addSeconds($value === null ? min(60, $this->ttl()) : $this->ttl())
            );

            return $this->memo[$cacheKey] = $value;
        } catch (AuthenticationException $e) {
            throw $e; // a stale session must reach the framework, not be retried
        } catch (Throwable) {
            // The cache store itself is broken — serve live data rather than 500.
            return $this->memo[$cacheKey] = $resolver();
        }
    }

    /**
     * Invalidate every cached read. Called after any successful write, and by the
     * "clear cache" button.
     */
    public function flush(): void
    {
        $this->memo = [];

        try {
            $next = $this->generation() + 1;
            Cache::forever($this->generationKey(), $next);
            $this->generation = $next;
        } catch (Throwable) {
            // A dead cache store means nothing is cached anyway.
            $this->generation = null;
        }
    }

    public function generation(): int
    {
        if ($this->generation !== null) {
            return $this->generation;
        }

        try {
            return $this->generation = max(1, (int) Cache::get($this->generationKey(), 1));
        } catch (Throwable) {
            return $this->generation = 1;
        }
    }

    private function key(string $key): string
    {
        return implode('.', [
            config('saas.cache.prefix', 'saas_content'),
            'v'.$this->generation(),
            $this->scope(),
            $key,
        ]);
    }

    private function generationKey(): string
    {
        return config('saas.cache.prefix', 'saas_content').'.generation.'.$this->scope();
    }

    /** Cached content is per-host: one deployment can serve several domains. */
    private function scope(): string
    {
        return md5(request()?->getHost() ?? (string) config('app.url'));
    }
}
