<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\QuestionBank\Database\Factories\QuizFactory;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'quizzes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'created_by',
        'priority',
        'title',
        'description',
        'guidelines',
        'show_description_on_course_page',
        'type',
        'question_ids',
        'start_time',
        'end_time',
        'has_time_limit',
        'time_limit_value',
        'time_limit_unit',
        'on_expiry',
        'marks_per_question',
        'negative_marks_per_wrong_answer',
        'pass_mark',
        'attempts_allowed',
        'enable_negative_marking',
        'result_visibility',
        'layout_pages',
        'shuffle_questions',
        'shuffle_options',
        'access_type',
        'access_password',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'question_ids' => 'array',
        'enable_negative_marking' => 'boolean',
        'show_description_on_course_page' => 'boolean',
        'has_time_limit' => 'boolean',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'time_limit_value' => 'integer',
        'marks_per_question' => 'float',
        'negative_marks_per_wrong_answer' => 'float',
        'pass_mark' => 'float',
        'attempts_allowed' => 'integer',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'id', 'question_ids');
    }

    public function attemptsAllowed()
    {
        return $this->hasMany(QuizAttempt::class, 'id', 'attempt_id');
    }

    protected static function newFactory(): QuizFactory
    {
        return QuizFactory::new();
    }
}
