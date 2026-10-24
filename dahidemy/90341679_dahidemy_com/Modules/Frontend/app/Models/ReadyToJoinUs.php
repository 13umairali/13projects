<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;

class ReadyToJoinUs extends Model
{
    protected $table = 'ready_to_join_us';

    public const TABLE_NAME = 'ready_to_join_us';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'icon',
        'button_name',
        'button_link',
    ];
}
