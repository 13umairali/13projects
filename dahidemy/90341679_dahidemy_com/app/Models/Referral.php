<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;

class Referral extends Model
{
    protected $fillable = [
        'referrer_type', 'referrer_id', 'referrer_role', 'referral_code_used',
        'referred_institute_id', 'status', 'reward_type', 'reward_value',
        'institute_reward_type', 'institute_reward_value', 'institute_reward_status',
        'notes', 'converted_at', 'rewarded_at', 'institute_rewarded_at',
    ];

    protected $casts = [
        'reward_value' => 'decimal:2',
        'institute_reward_value' => 'decimal:2',
        'converted_at' => 'datetime',
        'rewarded_at' => 'datetime',
        'institute_rewarded_at' => 'datetime',
    ];

    public function referredInstitute(): BelongsTo
    {
        return $this->belongsTo(Institute::class, 'referred_institute_id');
    }

    /**
     * The actual referrer - an Institute if referrer_type is 'institute',
     * or a User (any role) if referrer_type is 'user'. Not a polymorphic
     * relation on purpose: keeping referrer_type as a plain readable
     * string ('institute'/'user') makes every report/filter query trivial
     * to write without needing Laravel's morph-map indirection.
     */
    public function referrer()
    {
        return $this->referrer_type === 'institute'
            ? Institute::find($this->referrer_id)
            : User::find($this->referrer_id);
    }

    public function markConverted(int $instituteId): void
    {
        $this->update([
            'status' => 'converted',
            'referred_institute_id' => $instituteId,
            'converted_at' => now(),
        ]);
    }

    public function markRewarded(): void
    {
        $this->update([
            'status' => 'rewarded',
            'rewarded_at' => now(),
        ]);
    }

    /**
     * The institute admin's own approval of THEIR OWN reward track -
     * entirely independent of markRewarded() above, which is the SaaS
     * admin's approval of the SaaS-level reward. One referral, two
     * separate people who each decide whether to pay their own reward.
     */
    public function markInstituteRewarded(): void
    {
        $this->update([
            'institute_reward_status' => 'rewarded',
            'institute_rewarded_at' => now(),
        ]);
    }

    public function rejectInstituteReward(): void
    {
        $this->update(['institute_reward_status' => 'rejected']);
    }
}