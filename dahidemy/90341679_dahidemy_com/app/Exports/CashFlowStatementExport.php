<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CashFlowStatementExport implements FromView
{
    public function __construct(protected $cashFlowStatement) {}

    public function view(): View
    {
        return view('accounting::accounting-report.core-report.cash_flow_statement', [
            'cashFlowStatement' => $this->cashFlowStatement,
        ]);
    }
}
