<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'user_id', 'place_at', 'title', 'image'];
}
