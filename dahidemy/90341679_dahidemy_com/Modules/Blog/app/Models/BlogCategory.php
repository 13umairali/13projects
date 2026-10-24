<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $table = 'blog_categories';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 2;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'slug',
        'status',
        'created_by',
    ];

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }
}
