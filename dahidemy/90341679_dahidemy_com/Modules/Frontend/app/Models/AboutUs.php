<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;

    protected $table = 'about_us';

    public const TABLE_NAME = 'about_us';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'image',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
