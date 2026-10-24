<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstituteReferralSetting extends Model
{
    protected $fillable = [
        'institute_id', 'referrer_role', 'reward_type', 'reward_value', 'is_percentage', 'status', 'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_percentage' => 'boolean',
        'reward_value' => 'decimal:2',
    ];

    public static function forInstituteAndRole(int $instituteId, string $role): ?self
    {
        return static::where('institute_id', $instituteId)
            ->where('referrer_role', $role)
            ->where('status', true)
            ->first();
    }
}