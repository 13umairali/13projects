<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Authentication\Models\User;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id', 'institute_id', 'amount', 'status',
        'payout_method', 'payout_details',
        'payment_method', 'payment_reference', 'notes', 'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}