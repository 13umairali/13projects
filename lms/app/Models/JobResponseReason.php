<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class JobResponseReason extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'job_response_reasons';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public $translatedAttributes = ['title'];
    public $translationForeignKey = 'job_response_reason_id';

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }


    /* ==========
    | Relations
    * ==========*/


    /* ==========
     | Helpers
     * ==========*/




}
