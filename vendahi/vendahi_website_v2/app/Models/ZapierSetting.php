<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZapierSetting extends Model
{
    protected $fillable = [
        'enabled', 'api_key_hash', 'api_key_last4', 'user_id',
        'key_generated_at', 'last_used_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'key_generated_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /** The single settings row for this tenant (created on first use). */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
