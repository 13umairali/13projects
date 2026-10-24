<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CashSummaryExport implements FromView
{
    public function __construct(protected $fromDate, protected $toDate, protected $ledgers, protected $income, protected $expense) {}

    public function view(): View
    {
        return view('accounting::accounting-report.core-report.cash-summary', [
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'ledgers' => $this->ledgers,
            'income' => $this->income,
            'expense' => $this->expense,
        ]);
    }
}
