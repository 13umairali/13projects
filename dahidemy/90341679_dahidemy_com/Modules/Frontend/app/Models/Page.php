<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'slug',
        'type',
        'content',
        'status',
        'meta_data',
        'seo_meta_keywords',
        'seo_meta_description',
        'author_id',
    ];
}
