<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtocolRequestAttachment extends Model
{
    protected $table = 'protocol_request_attachments';

    protected $fillable = [
        'request_id', 'event_id', 'file_name', 'file_path', 'file_type', 'file_size',
        'uploaded_by_user_id', 'uploaded_by_client_id',
    ];

    public function request()
    {
        return $this->belongsTo(ProtocolRequest::class, 'request_id');
    }

    public function event()
    {
        return $this->belongsTo(ProtocolRequestEvent::class, 'event_id');
    }

    /** Public URL (files live under public/images/tenants/{id}/protocol). */
    public function url(): string
    {
        return url('/'.ltrim((string) $this->file_path, '/'));
    }
}
