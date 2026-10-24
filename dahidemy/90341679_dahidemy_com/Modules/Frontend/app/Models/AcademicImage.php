<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicImage extends Model
{
    protected $table = 'academic_images';

    public const TABLE_NAME = 'academic_images';

    protected $fillable = [
        'institute_id',
        'title',
        'heading',
        'description',
        'image',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
