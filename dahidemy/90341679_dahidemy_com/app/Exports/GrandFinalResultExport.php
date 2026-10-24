<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Same approach and reasoning as TabulationSheetExport: reuses the
 * already-correct print/PDF view instead of re-implementing the result
 * calculation output a second time in a hand-built export class.
 */
class GrandFinalResultExport implements FromView
{
    public function __construct(protected $results) {}

    public function view(): View
    {
        return view('examination::grand_final_result.result_pdf', ['results' => $this->results]);
    }
}
