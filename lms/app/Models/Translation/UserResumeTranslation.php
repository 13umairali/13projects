<?php

namespace App\Models\Translation;


use Illuminate\Database\Eloquent\Model;

class UserResumeTranslation extends Model
{

    protected $table = 'user_resume_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
