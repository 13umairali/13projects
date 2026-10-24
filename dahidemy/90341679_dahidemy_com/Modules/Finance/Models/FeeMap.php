<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Accounting\Models\AccountingLedger;

class FeeMap extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'fee_head_id', 'ledger_id', 'fund', 'type'];

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }

    public function feeLedger(): BelongsTo
    {
        return $this->belongsTo(AccountingLedger::class, 'ledger_id');
    }

    public function feeSubHeads(): BelongsToMany
    {
        return $this->belongsToMany(FeeSubHead::class, 'fee_map_fee_sub_head', 'fee_map_id', 'fee_sub_head_id');
    }

    public function funds(): BelongsToMany
    {
        return $this->belongsToMany(FeeMapFund::class, 'fee_map_fund', 'fee_map_id', 'fund_id');
    }
}
