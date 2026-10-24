<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TrialBalanceExport implements FromView
{
    public function __construct(protected $startDate, protected $endDate, protected $ledgers) {}

    public function view(): View
    {
        return view('accounting::accounting-report.core-report.trial-balance-overview', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'ledgers' => $this->ledgers,
        ]);
    }
}
