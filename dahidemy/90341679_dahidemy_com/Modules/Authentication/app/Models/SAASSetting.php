<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class SAASSetting extends Model
{
    protected $table = 's_a_a_s_settings';

    protected $guarded = ['id'];

    // cast value will be json decode
    protected $casts = [
        'payment_info' => 'array',
        'sms_info' => 'array',
    ];
}
