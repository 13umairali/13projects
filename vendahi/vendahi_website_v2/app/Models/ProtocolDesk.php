<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A desk in the protocol chain (Secretary, Administrator, CEO, …). Requests
 * move from desk to desk; the users attached to a desk are its officers.
 */
class ProtocolDesk extends Model
{
    use SoftDeletes;

    protected $table = 'protocol_desks';

    protected $fillable = ['name', 'description', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'protocol_desk_users', 'desk_id', 'user_id')->withTimestamps();
    }

    public function requests()
    {
        return $this->hasMany(ProtocolRequest::class, 'current_desk_id');
    }

    /** The entry desk: first active desk in chain order. */
    public static function entryDesk(): ?self
    {
        return static::where('is_active', true)->orderBy('sort_order')->orderBy('id')->first();
    }

    /** Ids of the desks a user sits on. */
    public static function idsForUser(int $userId): array
    {
        return \Illuminate\Support\Facades\DB::table('protocol_desk_users')
            ->where('user_id', $userId)
            ->pluck('desk_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
