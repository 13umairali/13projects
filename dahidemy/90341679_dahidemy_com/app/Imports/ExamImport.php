<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExamImport implements ToCollection, WithHeadingRow
{
    public array $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['student_id'])) {
                continue;
            }

            $this->data[] = [
                'student_id' => (int) $row['student_id'],
                'class_id' => (int) ($row['class_id'] ?? 0),
                'group_id' => (int) ($row['group_id'] ?? 0),
                'subject_id' => (int) ($row['subject_id'] ?? 0),
                'exam_id' => (int) ($row['exam_id'] ?? 0),
                'mark_1' => $row['mark_1'] ?? null,
                'mark_2' => $row['mark_2'] ?? null,
                'mark_3' => $row['mark_3'] ?? null,
                'mark_4' => $row['mark_4'] ?? null,
                'mark_5' => $row['mark_5'] ?? null,
                'mark_6' => $row['mark_6'] ?? null,
            ];
        }
    }
}
