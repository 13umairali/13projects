<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CashBookExport implements FromView
{
    public function __construct(protected $cashBookEntries) {}

    public function view(): View
    {
        return view('accounting::accounting-report.core-report.cash_book', [
            'cashBookEntries' => $this->cashBookEntries,
        ]);
    }
}
