<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankTag extends Model
{
    protected $fillable = [
        'tag_name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_tags';

    protected $table = self::TABLE_NAME;
}
