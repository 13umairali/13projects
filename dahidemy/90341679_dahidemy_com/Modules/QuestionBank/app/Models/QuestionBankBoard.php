<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankBoard extends Model
{
    protected $fillable = [
        'board',
        'short_name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_boards';

    protected $table = self::TABLE_NAME;
}
