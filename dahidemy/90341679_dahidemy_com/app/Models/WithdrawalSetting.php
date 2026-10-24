<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalSetting extends Model
{
    protected $fillable = ['scope_type', 'scope_id', 'min_amount', 'max_amount', 'enabled_methods'];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'enabled_methods' => 'array',
    ];

    /**
     * All four methods the request form and validation currently know how
     * to collect structured details for. If neither the platform nor an
     * institute has configured anything yet, everything is available and
     * unlimited - restrictions are opt-in, not opt-out.
     */
    public const ALL_METHODS = ['bank_transfer', 'paypal', 'mobile_money', 'other'];

    /**
     * The actual limits/methods that apply to a given institute's users:
     * an institute-specific value wins wherever it's actually set: a
     * value the institute left blank falls through to the platform's own
     * value for that same field, and finally to the hard-coded default
     * if neither has ever been configured. Fields are merged
     * independently - an institute can set only min_amount while
     * inheriting the platform's max_amount and enabled_methods, say.
     */
    public static function effectiveFor(?int $instituteId): array
    {
        $platform = static::where('scope_type', 'platform')->first();
        $institute = $instituteId
            ? static::where('scope_type', 'institute')->where('scope_id', $instituteId)->first()
            : null;

        return [
            'min_amount' => $institute?->min_amount ?? $platform?->min_amount ?? 0.00,
            'max_amount' => $institute?->max_amount ?? $platform?->max_amount, // null = unlimited
            'enabled_methods' => $institute?->enabled_methods ?? $platform?->enabled_methods ?? self::ALL_METHODS,
        ];
    }
}