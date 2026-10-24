<?php

namespace Modules\Elearning\Models;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    protected $fillable = [
        'institute_id',
        'name',
        'serial',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'lms_days';

    protected $table = self::TABLE_NAME;

    public function routines()
    {
        return $this->belongsToMany(ClassRoutine::class, 'class_routine_days', 'day_id', 'routine_id');
    }
}
