<?php

namespace Modules\LayoutCert\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CertificateIssue extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'type',
        'verification_code',
        'student_id',
        'subject_name',
        'snapshot',
        'issued_by',
        'issued_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'issued_at' => 'datetime',
    ];

    public static function record(string $type, ?int $studentId, string $subjectName, array $snapshot): self
    {
        return static::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'type' => $type,
            'verification_code' => strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)),
            'student_id' => $studentId,
            'subject_name' => $subjectName,
            'snapshot' => $snapshot,
            'issued_by' => getUserId(),
            'issued_at' => now(),
        ]);
    }
}
