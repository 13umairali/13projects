<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Picklist extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $table = 'picklists';
}
