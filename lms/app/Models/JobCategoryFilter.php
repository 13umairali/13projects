<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class JobCategoryFilter extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'job_category_filters';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public $translatedAttributes = ['title'];
    public $translationForeignKey = 'job_category_filter_id';

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }


    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(JobCategoryFilterOption::class, 'filter_id', 'id');
    }


}
