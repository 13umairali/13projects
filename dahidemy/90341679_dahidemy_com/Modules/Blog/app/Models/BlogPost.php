<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $table = 'blog_posts';

    public const STATUS_PUBLISHED = 1;

    public const STATUS_DRAFT = 2;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_name',
        'status',
        'published_at',
        'views',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function isPublished(): bool
    {
        return (int) $this->status === self::STATUS_PUBLISHED;
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function readingTimeMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->content));

        return max(1, (int) ceil($words / 200));
    }
}
