<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'reference_type',
        'reference_id',
        'phone',
        'message',
        'status',
        'response',
        'retry_count',
    ];

    public const TABLE_NAME = 'whats_app_logs';

    protected $table = self::TABLE_NAME;

    public function student()
    {
        return $this->belongsTo(
            Student::class,
            'student_id'
        );
    }
}
