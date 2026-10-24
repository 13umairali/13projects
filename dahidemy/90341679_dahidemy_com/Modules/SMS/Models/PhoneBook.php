<?php

namespace Modules\SMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'phone',
        'note',
        'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PhoneBookCategory::class);
    }
}
