<?php

namespace Modules\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankGroup extends Model
{
    protected $fillable = [
        'name',
        'class_id',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_groups';

    protected $table = self::TABLE_NAME;

    public function questions()
    {
        return $this->hasMany(Question::class, 'question_bank_group_id');
    }

    public function subjects()
    {
        return $this->hasMany(QuestionBankSubject::class, 'id');
    }
}
