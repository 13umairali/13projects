<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $connection = 'central';

    protected $table = 'blog_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'blog_category_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
