<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbrSetting extends Model
{
    protected $fillable = [
        'enabled', 'auto_submit', 'environment', 'bearer_token',
        'seller_ntn_cnic', 'seller_business_name', 'seller_province', 'seller_address',
        'scenario_id', 'default_hs_code', 'default_uom', 'default_sale_type',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'auto_submit' => 'boolean',
        'bearer_token' => 'encrypted',
    ];

    /**
     * Current tenant's FBR settings row (single-row table), created lazily.
     * Returns null when the table does not exist yet (tenant not migrated).
     */
    public static function current(): ?self
    {
        try {
            $row = static::query()->first();
            if (! $row) {
                $row = static::query()->create([
                    'environment' => config('fbr.default_environment', 'sandbox'),
                ]);
            }

            return $row;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Whether the integration is enabled and has everything needed to
     * submit invoices to the FBR gateway.
     */
    public function isReady(): bool
    {
        return $this->enabled
            && ! empty($this->bearer_token)
            && ! empty($this->seller_ntn_cnic)
            && ! empty($this->seller_province);
    }

    public function postUrl(): string
    {
        return $this->endpointUrl('post_path');
    }

    public function validateUrl(): string
    {
        return $this->endpointUrl('validate_path');
    }

    protected function endpointUrl(string $key): string
    {
        $env = config('fbr.environments.'.$this->environment)
            ?: config('fbr.environments.sandbox');

        return rtrim(config('fbr.base_url'), '/').$env[$key];
    }
}
