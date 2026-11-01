<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class JobEmploymentTypeTranslation extends Model
{
    protected $table = 'job_employment_type_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
