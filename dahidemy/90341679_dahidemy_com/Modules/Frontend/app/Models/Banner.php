<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $table = 'banners';

    public const TABLE_NAME = 'banners';

    protected $fillable = [
        'institute_id',
        'title',
        'description',
        'button_name',
        'button_link',
        'image',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
