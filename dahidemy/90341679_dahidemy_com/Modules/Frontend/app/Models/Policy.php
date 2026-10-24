<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    protected $table = 'policies';

    public const TABLE_NAME = 'policies';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'type',
        'description',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
