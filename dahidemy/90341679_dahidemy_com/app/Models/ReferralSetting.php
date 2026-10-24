<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralSetting extends Model
{
    protected $fillable = ['referrer_role', 'reward_type', 'reward_value', 'status', 'notes'];

    protected $casts = [
        'status' => 'boolean',
        'reward_value' => 'decimal:2',
    ];

    public static function forRole(string $role): ?self
    {
        return static::where('referrer_role', $role)->where('status', true)->first();
    }
}