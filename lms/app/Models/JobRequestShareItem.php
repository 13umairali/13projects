<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Google\Service\Classroom\Course;
use Illuminate\Database\Eloquent\Model;

class JobRequestShareItem extends Model
{
    protected $table = 'job_request_share_items';
    public $timestamps = false;
    protected $guarded = ['id'];


    /*===========
    |  Relations
     * =========*/
    public function course()
    {
        return $this->belongsTo(Webinar::class, 'course_id', 'id');
    }

    public function certificate()
    {
        return $this->belongsTo(Certificate::class, 'certificate_id', 'id');
    }
}
