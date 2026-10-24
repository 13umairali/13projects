<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LedgerBookExport implements FromView
{
    public function __construct(protected $ledgerBookEntries) {}

    public function view(): View
    {
        return view('accounting::accounting-report.core-report.ledger_book', [
            'ledgerBookEntries' => $this->ledgerBookEntries,
        ]);
    }
}
