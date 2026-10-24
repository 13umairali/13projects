<?php

namespace Modules\Examination\Models;

use Illuminate\Database\Eloquent\Model;

class MeritProcessType extends Model
{
    protected $fillable = [
        'institute_id',
        'branch_id',
        'type',
        'serial',
        'session_id',
    ];
}
