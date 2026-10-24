<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankSession extends Model
{
    protected $fillable = [
        'institute_id',
        'name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_sessions';

    protected $table = self::TABLE_NAME;
}
