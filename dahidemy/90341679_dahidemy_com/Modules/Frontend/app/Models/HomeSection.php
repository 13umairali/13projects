<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'serial',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'home_sections';

    protected $table = self::TABLE_NAME;
}
