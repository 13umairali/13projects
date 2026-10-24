<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingCategory extends Model
{
    protected $fillable = ['institute_id', 'branch_id', 'name', 'code', 'type', 'nature'];

    public function accountingGroups(): HasMany
    {
        return $this->hasMany(AccountingGroup::class, 'accounting_category_id', 'id')->with('accountingLedgers');
    }
}
