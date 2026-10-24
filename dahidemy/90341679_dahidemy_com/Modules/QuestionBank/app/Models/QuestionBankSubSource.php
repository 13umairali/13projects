<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankSubSource extends Model
{
    protected $fillable = [
        'source_id',
        'sub_source_name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_sub_sources';

    protected $table = self::TABLE_NAME;
}
