<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class JobEmploymentType extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'job_employment_types';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public $translatedAttributes = ['title'];
    public $translationForeignKey = 'job_employment_type_id';

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'employment_type_id', 'id');
    }


    /* ==========
     | Helpers
     * ==========*/

    public function getIconText()
    {
        $icon = $this->icon;

        if (empty($icon)) {
            $icon = "lin-monitor";
        }

        return $icon;
    }

}
