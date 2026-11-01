<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class JobCategoryFilterOptionTranslation extends Model
{
    protected $table = 'job_category_filter_option_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
