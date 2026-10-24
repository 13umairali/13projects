<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentListExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $students) {}

    public function collection(): Collection
    {
        return $this->students;
    }

    public function headings(): array
    {
        return ['#', 'Name', 'Admission No', 'Roll', 'Class', 'Section', 'Group', 'Phone', 'Gender', 'Status'];
    }

    public function map($student): array
    {
        return [
            $student['id'],
            trim(($student['first_name'] ?? '').' '.($student['last_name'] ?? '')),
            $student['registration_no'] ?? null,
            $student['roll'] ?? null,
            $student['class_name'] ?? null,
            $student['section_name'] ?? null,
            $student['group_name'] ?? null,
            $student['phone'] ?? null,
            $student['gender'] ?? null,
            ($student['status'] ?? null) == 1 ? 'Active' : 'Inactive',
        ];
    }
}
