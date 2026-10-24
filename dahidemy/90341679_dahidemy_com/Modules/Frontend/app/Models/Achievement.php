<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'image',
        'graduates',
        'certified_teachers',
        'student_campus',
        'students',
        'status',
    ];

    public const TABLE_NAME = 'achievements';

    protected $table = self::TABLE_NAME;
}
