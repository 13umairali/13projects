<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The bridge between an ERP record and its counterpart in one Shopify shop.
 *
 * Deliberately a table rather than a column on each local table: the same
 * product published to three shops has three different Shopify ids, which a
 * single column cannot hold. See the migration for the full reasoning.
 */
class ShopifyLink extends Model
{
    protected $table = 'shopify_links';

    protected $fillable = [
        'store_id',
        'entity_type',
        'local_id',
        'shopify_id',
        'shopify_handle',
        'secondary_id',
        'push_hash',
        'last_synced_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(ShopifyStore::class, 'store_id');
    }

    /**
     * Record (or refresh) the mapping for one record.
     *
     * Two unique indexes guard this table — one per local record, one per
     * remote record — and `firstOrNew` only honours the first of them. So a
     * remote id already claimed by a *different* local record used to abort
     * the whole sync with a duplicate-key error: the shop's product was
     * matched to a second ERP product (two products sharing a SKU, or the
     * originally linked one having since been deleted), and the insert hit
     * the remote index.
     *
     * A remote record maps to exactly one local record, so the honest
     * resolution is to move the mapping rather than to fail. The old row is
     * removed and the move is logged, because it means something worth
     * knowing about the catalogue.
     */
    public static function link(int $storeId, string $entity, int $localId, $shopifyId, array $extra = []): self
    {
        $shopifyId = (string) $shopifyId;

        $claimedBy = static::where('store_id', $storeId)
            ->where('entity_type', $entity)
            ->where('shopify_id', $shopifyId)
            ->where('local_id', '!=', $localId)
            ->get();

        foreach ($claimedBy as $stale) {
            ShopifyLog::write($storeId, 'link.move',
                ucfirst($entity).' '.$shopifyId.' was mapped to local #'.$stale->local_id
                    .' and is now mapped to #'.$localId.'. Usually two records share a code, or the first one was deleted.',
                'warning',
                ['entity' => $entity, 'shopify_id' => $shopifyId, 'from' => $stale->local_id, 'to' => $localId],
                null, $entity);

            $stale->delete();
        }

        $link = static::firstOrNew([
            'store_id' => $storeId,
            'entity_type' => $entity,
            'local_id' => $localId,
        ]);

        $link->shopify_id = (string) $shopifyId;
        $link->shopify_handle = $extra['handle'] ?? $link->shopify_handle;
        $link->secondary_id = $extra['secondary_id'] ?? $link->secondary_id;
        $link->push_hash = $extra['push_hash'] ?? $link->push_hash;
        $link->last_synced_at = now();
        $link->save();

        return $link;
    }

    public static function findLocal(int $storeId, string $entity, int $localId): ?self
    {
        return static::where('store_id', $storeId)
            ->where('entity_type', $entity)
            ->where('local_id', $localId)
            ->first();
    }

    public static function findRemote(int $storeId, string $entity, $shopifyId): ?self
    {
        return static::where('store_id', $storeId)
            ->where('entity_type', $entity)
            ->where('shopify_id', (string) $shopifyId)
            ->first();
    }

    /** Local id for a remote id, or null when the record is not linked yet. */
    public static function localIdFor(int $storeId, string $entity, $shopifyId): ?int
    {
        $link = static::findRemote($storeId, $entity, $shopifyId);

        return $link ? (int) $link->local_id : null;
    }
}
