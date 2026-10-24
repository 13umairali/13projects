<?php

namespace Modules\Elearning\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
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

    public const TABLE_NAME = 'lms_rooms';

    protected $table = self::TABLE_NAME;
}
