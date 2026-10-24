<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankDifficultyLevel extends Model
{
    protected $fillable = [
        'level_name',
        'level_code',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_difficulty_levels';

    protected $table = self::TABLE_NAME;
}
