<?php

namespace Modules\SystemConfiguration\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $guarded = ['id'];

    // cast value will be json decode
    protected $casts = [
        'payment_info' => 'array',
        'sms_info' => 'array',
    ];
}
