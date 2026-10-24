<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Authentication\Models\User;

class LibraryMember extends Model
{
    protected $table = 'library_members';

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name', 'phone', 'email', 'image');
    }
}
