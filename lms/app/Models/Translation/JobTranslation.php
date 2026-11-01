<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class JobTranslation extends Model
{
    protected $table = 'job_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
