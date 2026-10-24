<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;

class MobileAppSection extends Model
{
    protected $table = 'mobile_app_sections';

    public const TABLE_NAME = 'mobile_app_sections';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'heading',
        'description',
        'image',
        'feature_one',
        'feature_two',
        'feature_three',
        'play_store_link',
        'app_store_link',
    ];
}
