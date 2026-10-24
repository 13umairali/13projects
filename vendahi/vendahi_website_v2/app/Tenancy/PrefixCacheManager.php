<?php

declare(strict_types=1);

namespace App\Tenancy;

use Illuminate\Cache\CacheManager as BaseCacheManager;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\Repository;

/**
 * A tenant-aware CacheManager that isolates cache per tenant without tags.
 *
 * - FileStore: uses a tenant-specific subdirectory.
 * - Stores with setPrefix (Redis, Memcached, etc.): prefixes the key.
 * - DatabaseStore: prefixes via key namespace.
 */
class PrefixCacheManager extends BaseCacheManager
{
    protected ?string $tenantPrefix = null;

    /** Store names already prefixed — parent::store() caches repositories, so
     *  the suffix must be applied exactly once per store, not on every call. */
    protected array $prefixedStores = [];

    public function setTenantPrefix(string $prefix): void
    {
        $this->tenantPrefix = $prefix;
    }

    public function store($name = null): Repository
    {
        $name = $name ?: $this->getDefaultDriver();
        $store = parent::store($name);

        if ($this->tenantPrefix && ! isset($this->prefixedStores[$name])) {
            $this->prefixedStores[$name] = true;
            $inner = $store->getStore();

            if ($inner instanceof FileStore) {
                $inner->setDirectory($inner->getDirectory() . '/' . $this->tenantPrefix);
            } elseif (method_exists($inner, 'setPrefix')) {
                $currentPrefix = $inner->getPrefix();
                $inner->setPrefix($currentPrefix . $this->tenantPrefix . ':');
            }
        }

        return $store;
    }
}
