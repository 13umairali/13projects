<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class JobCategoryFilterOption extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'job_category_filter_options';
    public $timestamps = false;
    protected $guarded = ['id'];

    public $translatedAttributes = ['title'];
    public $translationForeignKey = 'filter_option_id';

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }


}
