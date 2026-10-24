<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A protocol request — complaint, request, memo, letter, administrative
 * document, enquiry or other correspondence — moving desk to desk.
 */
class ProtocolRequest extends Model
{
    use SoftDeletes;

    public const TYPES = ['complaint', 'request', 'memo', 'letter', 'document', 'enquiry', 'other'];

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public const STATUSES = ['open', 'received', 'acknowledged', 'in_progress', 'approved', 'rejected', 'closed'];

    /** Statuses in which the request is still moving. */
    public const ACTIVE_STATUSES = ['open', 'received', 'acknowledged', 'in_progress'];

    protected $table = 'protocol_requests';

    protected $fillable = [
        'Ref', 'type', 'subject', 'body', 'priority', 'status',
        'submitted_by_user_id', 'client_id', 'submitter_name', 'submitter_email', 'channel',
        'current_desk_id', 'previous_desk_id', 'assigned_to_user_id', 'due_date',
        'received_at', 'acknowledged_at', 'decided_at', 'closed_at', 'closed_by_user_id', 'last_activity_at',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'received_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'decided_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function events()
    {
        return $this->hasMany(ProtocolRequestEvent::class, 'request_id')->orderBy('id');
    }

    public function attachments()
    {
        return $this->hasMany(ProtocolRequestAttachment::class, 'request_id')->orderBy('id');
    }

    public function currentDesk()
    {
        return $this->belongsTo(ProtocolDesk::class, 'current_desk_id')->withTrashed();
    }

    public function previousDesk()
    {
        return $this->belongsTo(ProtocolDesk::class, 'previous_desk_id')->withTrashed();
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    /** Display name of whoever submitted the request. */
    public function submitterLabel(): string
    {
        if ($this->submitter_name) {
            return $this->submitter_name;
        }
        if ($this->client) {
            return (string) $this->client->name;
        }
        if ($this->submitter) {
            return (string) $this->submitter->username;
        }

        return '---';
    }
}
