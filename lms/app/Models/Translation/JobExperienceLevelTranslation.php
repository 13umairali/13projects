<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class JobExperienceLevelTranslation extends Model
{
    protected $table = 'job_experience_level_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
