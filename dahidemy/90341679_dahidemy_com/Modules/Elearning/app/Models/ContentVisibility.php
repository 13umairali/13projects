<?php

namespace Modules\Elearning\Models;

use Illuminate\Database\Eloquent\Model;

class ContentVisibility extends Model
{
    protected $guarded = ['id'];

    public const TABLE_NAME = 'content_visibility';

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
