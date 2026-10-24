<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankType extends Model
{
    protected $fillable = [
        'type_name',
        'default_mark',
        'description',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_types';

    protected $table = self::TABLE_NAME;
}
