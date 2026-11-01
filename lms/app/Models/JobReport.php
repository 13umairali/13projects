<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobReport extends Model
{
    protected $table = 'job_reports';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }
}
