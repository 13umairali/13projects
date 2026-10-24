<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookIssueExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $issues) {}

    public function collection(): Collection
    {
        return $this->issues;
    }

    public function headings(): array
    {
        return ['#', 'Book Code', 'Library ID', 'Book Name', 'Issue Date', 'Due Date', 'Return Date'];
    }

    public function map($issue): array
    {
        return [
            $issue->id,
            $issue->code,
            $issue->library_id,
            $issue->book_name,
            $issue->issue_date ? date('Y-m-d', strtotime($issue->issue_date)) : '-',
            $issue->due_date ? date('Y-m-d', strtotime($issue->due_date)) : '-',
            $issue->return_date ? date('Y-m-d', strtotime($issue->return_date)) : '-',
        ];
    }
}
