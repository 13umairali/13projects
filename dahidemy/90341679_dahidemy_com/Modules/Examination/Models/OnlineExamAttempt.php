<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\Student;

class OnlineExamAttempt extends Model
{
    use BelongsToTenant;

    public const STATUS_STARTED = 'started';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_AUTO_SUBMITTED = 'auto_submitted';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'online_exam_id',
        'student_id',
        'user_id',
        'answers',
        'score',
        'converted_score',
        'correct_count',
        'incorrect_count',
        'skipped_count',
        'status',
        'started_at',
        'expires_at',
        'submitted_at',
        'time_taken',
        'is_passed',
        'integrity_violations',
        'violation_count',
    ];

    protected $casts = [
        'answers' => 'array',
        'integrity_violations' => 'array',
        'score' => 'decimal:2',
        'converted_score' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'is_passed' => 'boolean',
    ];

    public function onlineExam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gte($this->expires_at);
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_AUTO_SUBMITTED], true);
    }

    /**
     * Append one integrity event and bump the counter. Never throws — a
     * logging hiccup should never be able to interrupt someone's exam.
     */
    public function logViolation(string $type, ?string $detail = null): void
    {
        $log = $this->integrity_violations ?? [];
        $log[] = [
            'type' => $type,
            'detail' => $detail,
            'at' => now()->toIso8601String(),
        ];

        $this->update([
            'integrity_violations' => $log,
            'violation_count' => count($log),
        ]);
    }
}
