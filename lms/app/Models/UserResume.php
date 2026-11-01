<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class UserResume extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = "user_resumes";
    public $timestamps = false;

    protected $guarded = ['id'];

    public $translatedAttributes = ['title'];


    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }


    /* ==========
     | Relations
     * ==========*/
    public function user()
    {
        return $this->belongsTo("\App\User", 'user_id', 'id');
    }

    /* ==========
     | Helpers
     * ==========*/
    public function getFileSize()
    {
        $size = "";
        $path = public_path($this->attach);

        if (file_exists($path)) {
            $filesize = filesize($path); // B

            $size = formatSizeUnits($filesize);
        }

        return $size;
    }

}
