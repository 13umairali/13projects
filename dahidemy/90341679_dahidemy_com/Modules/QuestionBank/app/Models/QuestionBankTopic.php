<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankTopic extends Model
{
    protected $fillable = [
        'question_bank_chapter_id',
        'name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_topics';

    protected $table = self::TABLE_NAME;
}
