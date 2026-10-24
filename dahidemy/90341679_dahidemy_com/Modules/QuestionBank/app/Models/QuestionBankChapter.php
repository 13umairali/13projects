<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankChapter extends Model
{
    protected $fillable = [
        'name',
        'subject_id',
        'status',
        'chapter_no',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_chapters';

    protected $table = self::TABLE_NAME;

    public function questions()
    {
        return $this->hasMany(Question::class, 'question_bank_chapter_id');
    }
}
