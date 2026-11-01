<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class JobRequest extends Model
{
    protected $table = 'job_requests';
    public $timestamps = false;
    protected $guarded = ['id'];


    /*===========
    |  Relations
     * =========*/
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }

    public function userResume()
    {
        return $this->belongsTo(UserResume::class, 'user_resume_id', 'id');
    }

    public function jobResponseReason()
    {
        return $this->belongsTo(JobResponseReason::class, 'job_reason_id', 'id');
    }

    public function certificates()
    {
        return $this->belongsToMany(Certificate::class, 'job_request_share_items', 'job_request_id', 'certificate_id');
    }

    public function learningCourses()
    {
        return $this->belongsToMany(Webinar::class, 'job_request_share_items', 'job_request_id', 'course_id');
    }

    public function attachments()
    {
        return $this->hasMany(JobRequestAttachment::class, 'job_request_id', 'id');
    }


    /*===========
    |  Helpers
     * =========*/

    public function getSharedInformation()
    {
        $contents = [];

        if (!empty($this->user_resume_id)) {
            $contents["resume"] = trans("update.resume");
        }

        if ($this->certificates()->count() > 0) {
            $contents["certificates"] = trans("update.certificates");
        }

        if ($this->learningCourses()->count() > 0) {
            $contents["course"] = trans("update.course");
        }

        if ($this->attachments()->count() > 0) {
            $contents["attachments"] = trans("update.attachments");
        }

        return $contents;
    }
}
