<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $table = 'testimonials';

    public const TABLE_NAME = 'testimonials';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'description',
        'thumbnail_image',
        'video_url',
        'note',
        'ratting',
        'status',
    ];
}
