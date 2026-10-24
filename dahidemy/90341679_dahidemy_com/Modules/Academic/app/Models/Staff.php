<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Database\Factories\StaffFactory;
use Modules\Authentication\Models\User;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staffs';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'user_id',
        'department_id',
        'staff_category_id',
        'name',
        'designation',
        'birthday',
        'gender',
        'religion',
        'phone',
        'blood',
        'sl',
        'address',
        'joining_date',
        'access_key',
        'status',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function staffCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StaffCategory::class, 'staff_category_id');
    }

    protected static function newFactory()
    {
        return StaffFactory::new();
    }
}
