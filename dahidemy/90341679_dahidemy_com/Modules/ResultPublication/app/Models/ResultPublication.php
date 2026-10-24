<?php

namespace Modules\ResultPublication\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Subject;
use Modules\Examination\Models\OnlineExam;

class ResultPublication extends Model
{
    protected $table = 'result_publications';

    public const STATUS_PENDING = 1;

    public const STATUS_APPROVED = 2;

    public const STATUS_REJECTED = 3;

    public const TYPE_OPTIONS = [
        'physical' => 'Physical / Scanned Result (upload a file)',
        'online' => 'Link to an Existing Online Exam Result',
        'summary' => 'Written Announcement Only',
    ];

    protected $fillable = [
        'institute_id',
        'branch_id',
        'class_id',
        'section_id',
        'subject_id',
        'exam_id',
        'online_exam_id',
        'title',
        'description',
        'type',
        'file',
        'status',
        'submitted_by',
        'reviewed_by',
        'remarks',
        'reviewed_at',
        'published_at',
        'notified_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function onlineExam()
    {
        return $this->belongsTo(OnlineExam::class, 'online_exam_id');
    }

    public function isPending(): bool
    {
        return (int) $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return (int) $this->status === self::STATUS_APPROVED;
    }

    public function statusLabel(): string
    {
        return match ((int) $this->status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Pending Review',
        };
    }

    public function scopeVisibleToClassSection($query, int $classId, ?int $sectionId)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('class_id', $classId)
            ->where(function ($q) use ($sectionId) {
                $q->whereNull('section_id');
                if ($sectionId) {
                    $q->orWhere('section_id', $sectionId);
                }
            })
            ->orderByDesc('published_at');
    }
}
