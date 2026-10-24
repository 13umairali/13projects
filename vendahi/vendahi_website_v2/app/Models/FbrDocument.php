<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FbrDocument extends Model
{
    protected $fillable = [
        'source_type', 'source_id', 'invoice_ref', 'invoice_type', 'fbr_invoice_number',
        'environment', 'status', 'status_code', 'error', 'request_payload', 'response',
        'submitted_at',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'request_payload' => 'array',
        'response' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function source()
    {
        return $this->morphTo();
    }

    public function logs()
    {
        return $this->hasMany(FbrLog::class);
    }

    public static function forSource(Model $source): ?self
    {
        try {
            return static::query()
                ->where('source_type', get_class($source))
                ->where('source_id', $source->id)
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isAccepted(): bool
    {
        return $this->status === 'valid' && ! empty($this->fbr_invoice_number);
    }
}
