<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BalanceSheetExport implements FromView
{
    public function __construct(protected $logo, protected $startDate, protected $endDate, protected $ledgers) {}

    public function view(): View
    {
        return view('accounting::accounting-report.core-report.balance-sheet', [
            'logo' => $this->logo,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'ledgers' => $this->ledgers,
        ]);
    }
}
