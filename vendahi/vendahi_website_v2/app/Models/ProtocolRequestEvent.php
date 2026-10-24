<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One movement in a request's history: who did what, at which desk, when.
 */
class ProtocolRequestEvent extends Model
{
    protected $table = 'protocol_request_events';

    public const ACTIONS = [
        'submitted', 'received', 'acknowledged', 'note', 'response', 'reply', 'forwarded',
        'reassigned', 'returned', 'approved', 'rejected', 'closed', 'reopened', 'attachment', 'updated',
    ];

    protected $fillable = [
        'request_id', 'action', 'user_id', 'client_id', 'from_desk_id', 'to_desk_id',
        'assigned_to_user_id', 'note', 'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function request()
    {
        return $this->belongsTo(ProtocolRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function fromDesk()
    {
        return $this->belongsTo(ProtocolDesk::class, 'from_desk_id')->withTrashed();
    }

    public function toDesk()
    {
        return $this->belongsTo(ProtocolDesk::class, 'to_desk_id')->withTrashed();
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function attachments()
    {
        return $this->hasMany(ProtocolRequestAttachment::class, 'event_id');
    }
}
