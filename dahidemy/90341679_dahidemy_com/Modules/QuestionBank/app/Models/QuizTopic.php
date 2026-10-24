<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuizTopic extends Model
{
    protected $fillable = [
        'quiz_id',
        'institute_id',
        'subject_id',
        'chapter_id',
        'question_category_id',
        'question_limit',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'quiz_topics';

    protected $table = self::TABLE_NAME;

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
