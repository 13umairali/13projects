<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MigratedListExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $migratedLists) {}

    public function collection(): Collection
    {
        return $this->migratedLists;
    }

    public function headings(): array
    {
        return ['#', 'Student', 'Admission No', 'Migrated From (Class/Section)', 'Migrated To (Class/Section)', 'Prev Roll', 'New Roll', 'Type', 'School (if external)', 'Date'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            trim(($row->first_name ?? '').' '.($row->last_name ?? '')),
            $row->registration_no,
            trim((optional($row->class)->class_name ?? '-').' / '.(optional($row->section)->section_name ?? '-')),
            trim((optional($row->migratedToClass)->class_name ?? '-').' / '.(optional($row->migratedToSection)->section_name ?? '-')),
            $row->prev_roll,
            $row->new_roll,
            ucfirst(str_replace('_', ' ', $row->type)),
            $row->transferred_to_school_name ?? '-',
            optional($row->migrated_at)->format('Y-m-d'),
        ];
    }
}
