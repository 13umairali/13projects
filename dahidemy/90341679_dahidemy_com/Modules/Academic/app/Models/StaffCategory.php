<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffCategory extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'sort_order',
    ];

    public function staffs(): HasMany
    {
        return $this->hasMany(Staff::class, 'staff_category_id');
    }
}
