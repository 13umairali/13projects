<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $connection = 'central';

    protected $table = 'blog_posts';

    protected $fillable = [
        'blog_category_id',
        'central_user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'meta_title',
        'meta_description',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(CentralUser::class, 'central_user_id');
    }

    // Publicly visible: published flag set AND publish date reached.
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getMetaTitleResolvedAttribute(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function getMetaDescriptionResolvedAttribute(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        return Str::limit(trim(strip_tags($this->excerpt ?: (string) $this->content)), 160, '');
    }
}
