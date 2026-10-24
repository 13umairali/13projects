<?php

namespace Modules\Examination\Services;

use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Models\Grade;

class GradeCalculationService
{
    /**
     * Resolve the grade for a given class and marks/percentage value.
     * Prefers the class-specific grading scale (ExamGrade) when one is
     * configured for the class, falling back to the global Grade scale.
     *
     * $instituteId is optional and, when provided, scopes the global-Grade
     * fallback to that institute (neither Grade nor ExamGrade carry an
     * automatic tenant scope), matching this app's other tenant-scoped
     * lookups.
     */
    public function resolveGrade(int $classId, float $marksOrPercentage, ?int $instituteId = null): ?Grade
    {
        $examGrade = ExamGrade::where('class_model_id', $classId)
            ->when($instituteId, fn ($query) => $query->where('institute_id', $instituteId))
            ->where('number_low', '<=', $marksOrPercentage)
            ->where('number_high', '>=', $marksOrPercentage)
            ->first();

        if ($examGrade) {
            return new Grade([
                'grade_name' => $examGrade->grade_name,
                'grade_number' => $examGrade->grade_number,
                'grade_point' => $examGrade->grade_point,
                'grade_range' => $examGrade->grade_range,
                'number_high' => $examGrade->number_high,
                'number_low' => $examGrade->number_low,
                'point_high' => $examGrade->point_high,
                'point_low' => $examGrade->point_low,
                'priority' => $examGrade->priority,
            ]);
        }

        return Grade::when($instituteId, fn ($query) => $query->where('institute_id', $instituteId))
            ->where('number_low', '<=', $marksOrPercentage)
            ->where('number_high', '>=', $marksOrPercentage)
            ->first();
    }

    public function calculatePercentage(float $obtained, float $fullMarks): float
    {
        if ($fullMarks <= 0) {
            return 0.0;
        }

        return round(($obtained / $fullMarks) * 100, 2);
    }
}
