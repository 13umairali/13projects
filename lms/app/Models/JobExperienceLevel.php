<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class JobExperienceLevel extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'job_experience_levels';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public $translatedAttributes = ['title'];
    public $translationForeignKey = 'job_experience_level_id';

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'experience_level_id', 'id');
    }


    /* ==========
     | Helpers
     * ==========*/

    public function getIconText()
    {
        $icon = $this->icon;

        if (empty($icon)) {
            $icon = "lin-user-octagon";
        }

        return $icon;
    }


}
