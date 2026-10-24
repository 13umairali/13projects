<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class IncomeStatementExport implements FromView
{
    public function __construct(protected $fromDate, protected $toDate, protected $ledgers, protected $income, protected $expense) {}

    public function view(): View
    {
        return view('accounting::accounting-report.core-report.income-statement', [
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'ledgers' => $this->ledgers,
            'income' => $this->income,
            'expense' => $this->expense,
        ]);
    }
}
