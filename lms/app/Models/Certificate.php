<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = "certificates";
    public $timestamps = false;
    protected $guarded = ['id'];

    public function quiz()
    {
        return $this->hasOne('App\Models\Quiz', 'id', 'quiz_id');
    }

    public function student()
    {
        return $this->hasOne('App\User', 'id', 'student_id');
    }

    public function quizzesResult()
    {
        return $this->hasOne('App\Models\QuizzesResult', 'id', 'quiz_result_id');
    }

    public function webinar()
    {
        return $this->belongsTo('App\Models\Webinar', 'webinar_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo('App\Models\Bundle', 'bundle_id', 'id');
    }

    /*===========
    |  Helpers
     * =========*/
    public function getItemTitle()
    {
        $title = null;

        if (!empty($this->quiz_id) and !empty($this->quiz)) {
            $title = $this->quiz->title;
        } elseif (!empty($this->bundle_id) and !empty($this->bundle)) {
            $title = $this->bundle->title;
        } elseif (!empty($this->webinar_id) and !empty($this->webinar)) {
            $title = $this->webinar->title;
        }

        return $title;
    }

    public function getItemIcon()
    {
        $icon = null;

        if (!empty($this->quiz_id) and !empty($this->quiz)) {
            $icon = $this->quiz->icon ?? null;
        } elseif (!empty($this->webinar_id) and !empty($this->webinar)) {
            $icon = $this->webinar->getIcon();
        }

        return $icon;
    }
}
