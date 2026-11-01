<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobSelectedFilterOption extends Model
{
    protected $table = 'job_selected_filter_options';
    public $timestamps = false;

    protected $guarded = ['id'];
}
