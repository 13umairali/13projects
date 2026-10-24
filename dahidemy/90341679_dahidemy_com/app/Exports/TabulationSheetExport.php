<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Renders the exact same Blade view used for print/PDF straight into an
 * Excel file. Chosen deliberately over a hand-built row-mapping export:
 * the tabulation sheet is a dynamically-built subject x student matrix
 * (see TabulationSheetService::build()), and re-implementing that
 * structure a second time in a separate export class would risk drifting
 * out of sync with the real thing or getting a column wrong. Reusing the
 * already-correct, already-tested view avoids that risk entirely.
 */
class TabulationSheetExport implements FromView
{
    public function __construct(protected array $sheet) {}

    public function view(): View
    {
        return view('examination::results.tabulation_print', ['sheet' => $this->sheet]);
    }
}
