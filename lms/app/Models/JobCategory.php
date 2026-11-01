<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class JobCategory extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'job_categories';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public $translatedAttributes = ['title', 'subtitle', 'bottom_seo_title', 'bottom_seo_description'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getSubtitleAttribute()
    {
        return getTranslateAttributeValue($this, 'subtitle');
    }

    public function getBottomSeoTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'bottom_seo_title');
    }

    public function getBottomSeoDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'bottom_seo_description');
    }


    public function category()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function subCategories()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->orderBy('order', 'asc');
    }

    public function filters()
    {
        return $this->hasMany(JobCategoryFilter::class, 'category_id', 'id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'category_id', 'id');
    }

    public function getUrl()
    {
        return '/jobs?category_id=' . $this->id;
    }

}
