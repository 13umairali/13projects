<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\StudentGroup;
use Modules\Academic\Models\Subject;
use Modules\QuestionBank\Models\Quiz;

class OnlineExam extends Model
{
    use BelongsToTenant;

    public const POLICY_SKIP = 'skip';

    public const POLICY_UPDATE = 'update';

    public const POLICY_ASK = 'ask';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'exam_id',
        'class_id',
        'section_id',
        'group_id',
        'subject_id',
        'quiz_id',
        'name',
        'duration_minutes',
        'start_at',
        'end_at',
        'total_questions',
        'total_marks',
        'pass_marks',
        'attempt_limit',
        'negative_marking',
        'negative_marks_per_wrong',
        'random_questions',
        'random_options',
        'conversion_enabled',
        'online_full_marks',
        'academic_full_marks',
        'transfer_policy',
        'mark_component_slot',
        'is_active',
        'proctoring_enabled',
        'max_violations_before_auto_submit',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'total_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2',
        'negative_marking' => 'boolean',
        'negative_marks_per_wrong' => 'decimal:2',
        'random_questions' => 'boolean',
        'random_options' => 'boolean',
        'conversion_enabled' => 'boolean',
        'online_full_marks' => 'decimal:2',
        'academic_full_marks' => 'decimal:2',
        'is_active' => 'boolean',
        'proctoring_enabled' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(OnlineExamQuestion::class)->orderBy('display_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(OnlineExamAttempt::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(OnlineExamMarkTransfer::class);
    }

    public function isOpen(): bool
    {
        $now = now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        return true;
    }
}
