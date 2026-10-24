<?php

namespace Modules\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicFormSubmission extends Model
{
    protected $table = 'form_builder_submissions';

    public const STATUS_PENDING = 1;

    public const STATUS_APPROVED = 2;

    public const STATUS_REJECTED = 3;

    protected $fillable = [
        'form_id',
        'institute_id',
        'data',
        'ip_address',
        'status',
        'remarks',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(DynamicForm::class, 'form_id');
    }

    public function isPending(): bool
    {
        return (int) $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return match ((int) $this->status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Pending',
        };
    }
}
