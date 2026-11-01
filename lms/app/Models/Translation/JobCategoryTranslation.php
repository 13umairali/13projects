<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class JobCategoryTranslation extends Model
{
    protected $table = 'job_category_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
