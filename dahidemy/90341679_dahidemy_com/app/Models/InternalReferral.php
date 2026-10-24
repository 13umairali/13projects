<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Authentication\Models\User;

class InternalReferral extends Model
{
    protected $fillable = [
        'institute_id', 'referrer_user_id', 'referrer_user_type', 'referral_code_used',
        'referred_user_id', 'referred_user_type', 'status',
        'reward_type', 'reward_value', 'notes', 'rewarded_at',
    ];

    protected $casts = [
        'reward_value' => 'decimal:2',
        'rewarded_at' => 'datetime',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function markRewarded(): void
    {
        $this->update(['status' => 'rewarded', 'rewarded_at' => now()]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }
}