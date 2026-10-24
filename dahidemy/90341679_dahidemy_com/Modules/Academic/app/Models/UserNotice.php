<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotice extends Model
{
    protected $table = 'user_notices';

    protected $guarded = ['id'];
}
