<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankLevel extends Model
{
    protected $fillable = [
        'level_name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_levels';

    protected $table = self::TABLE_NAME;
}
