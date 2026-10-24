<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbrLog extends Model
{
    protected $fillable = [
        'fbr_document_id', 'action', 'endpoint', 'http_status', 'request_meta', 'response_body',
    ];

    protected $casts = [
        'fbr_document_id' => 'integer',
        'http_status' => 'integer',
        'request_meta' => 'array',
    ];
}
