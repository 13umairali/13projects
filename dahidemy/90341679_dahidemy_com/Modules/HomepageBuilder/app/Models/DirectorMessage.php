<?php

namespace Modules\HomepageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class DirectorMessage extends Model
{
    protected $table = 'director_messages';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'director_name',
        'designation',
        'message',
        'photo',
        'created_by',
    ];

    public function hasContent(): bool
    {
        return ! empty($this->message);
    }
}
