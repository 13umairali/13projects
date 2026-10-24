<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = ['id'];

    protected $table = 'events';
}
