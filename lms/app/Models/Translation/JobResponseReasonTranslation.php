<?php

namespace App\Models\Translation;

use Illuminate\Database\Eloquent\Model;

class JobResponseReasonTranslation extends Model
{
    protected $table = 'job_response_reason_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
