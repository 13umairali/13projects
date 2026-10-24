<?php

namespace Modules\SMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneBookCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'description',
    ];
}
