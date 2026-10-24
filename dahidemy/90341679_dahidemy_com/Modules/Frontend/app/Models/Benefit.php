<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;

class Benefit extends Model
{
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'icon',
        'status',
    ];

    public const TABLE_NAME = 'benefits';

    protected $table = self::TABLE_NAME;
}
