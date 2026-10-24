<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;

class WhyChooseUs extends Model
{
    protected $table = 'why_choose_us';

    public const TABLE_NAME = 'why_choose_us';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'icon',
    ];
}
