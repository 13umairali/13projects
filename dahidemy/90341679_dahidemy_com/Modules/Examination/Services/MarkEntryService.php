<?php

namespace Modules\Examination\Services;

use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\MarkConfig;
use Modules\Examination\Models\RemarkConfig;

class MarkEntryService
{
    public function __construct(
        private readonly GradeCalculationService $gradeCalculationService,
        private readonly ExamContextService $examContextService,
    ) {}

    /**
     * The maximum total marks allowed for a subject's mark entry.
     * Sums the subject's configured MarkConfigExamCode components for the
     * exam when configured; falls back to 100 when nothing is configured
     * yet, matching the previous hard-coded behavior for those classes.
     */
    public function resolveMaxMarks(int $subjectId, int $examId): float
    {
        $configuredTotal = MarkConfig::where('subject_id', $subjectId)
            ->where('exam_id', $examId)
            ->with('mark_config_exam_code')
            ->get()
            ->pluck('mark_config_exam_code.total_marks')
            ->filter()
            ->sum();

        return $configuredTotal > 0 ? (float) $configuredTotal : 100.0;
    }

    public function resolvePassMarks(int $subjectId, int $examId): float
    {
        $configuredPass = MarkConfig::where('subject_id', $subjectId)
            ->where('exam_id', $examId)
            ->with('mark_config_exam_code')
            ->get()
            ->pluck('mark_config_exam_code.pass_mark')
            ->filter(fn ($value) => $value !== null)
            ->sum();

        return $configuredPass > 0 ? (float) $configuredPass : 33.0;
    }

    public function resolveComponents(int $subjectId, int $examId)
    {
        return MarkConfig::where('subject_id', $subjectId)
            ->where('exam_id', $examId)
            ->with('mark_config_exam_code')
            ->get();
    }

    /**
     * Save one student's section-wise marks for a subject/exam.
     *
     * @param  array  $identifiers  institute_id, branch_id, session_id, student_id, class_id, group_id, subject_id, exam_id
     * @param  array<int|string, mixed>  $markValuesByQuestion  question number (1-6) => mark value
     *
     * @throws \Exception when the total exceeds the resolved max marks
     */
    public function saveSectionWiseMarks(array $identifiers, array $markValuesByQuestion, array $meta = []): ExamMark
    {
        $isAbsent = (bool) ($meta['is_absent'] ?? false);
        $isExempted = (bool) ($meta['is_exempted'] ?? false);
        $source = $meta['source'] ?? ExamMark::SOURCE_MANUAL;

        $marksData = [];
        $totalMarks = 0.0;

        foreach ($markValuesByQuestion as $questionNumber => $mark) {
            $questionNumber = (int) $questionNumber;

            if ($questionNumber < 1 || $questionNumber > 6) {
                continue;
            }

            $markValue = (float) ($mark ?? 0);
            $marksData["mark{$questionNumber}"] = $markValue;
            $totalMarks += $markValue;
        }

        $maxMarks = $this->resolveMaxMarks((int) $identifiers['subject_id'], (int) $identifiers['exam_id']);
        $passMarks = $this->resolvePassMarks((int) $identifiers['subject_id'], (int) $identifiers['exam_id']);

        if (! $isAbsent && ! $isExempted && $totalMarks > $maxMarks) {
            throw new \Exception(
                "Total marks must not exceed {$maxMarks}. Student ID {$identifiers['student_id']} has {$totalMarks}"
            );
        }

        if ($isAbsent) {
            $totalMarks = 0.0;
            $percentage = 0.0;
            $status = ExamMark::STATUS_ABSENT;
            $gradeName = 'F';
            $gradePoint = 0.0;
        } elseif ($isExempted) {
            $percentage = 0.0;
            $status = ExamMark::STATUS_EXEMPTED;
            $gradeName = $meta['grade'] ?? 'EX';
            $gradePoint = (float) ($meta['grade_point'] ?? 0);
        } else {
            $percentage = $this->gradeCalculationService->calculatePercentage($totalMarks, $maxMarks);
            $grade = $this->gradeCalculationService->resolveGrade(
                (int) $identifiers['class_id'],
                $percentage,
                isset($identifiers['institute_id']) ? (int) $identifiers['institute_id'] : null,
            );
            $gradeName = $grade->grade_name ?? 'F';
            $gradePoint = (float) ($grade->grade_point ?? 0);
            $status = ($totalMarks < $passMarks || $gradeName === 'F')
                ? ExamMark::STATUS_FAIL
                : ExamMark::STATUS_PASS;
        }

        $remarks = $meta['remarks'] ?? RemarkConfig::where('remark_title', $gradeName)->value('remarks');

        $data = array_merge($marksData, [
            'class_id' => (int) $identifiers['class_id'],
            'group_id' => $this->examContextService->resolveGroupId(
                isset($identifiers['group_id']) ? (int) $identifiers['group_id'] : null
            ),
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'grade' => $gradeName,
            'grade_point' => $gradePoint,
            'status' => $status,
            'remarks' => $remarks,
            'is_absent' => $isAbsent,
            'is_exempted' => $isExempted,
            'source' => $source,
        ]);

        $lookupKeys = $this->examContextService->markLookupKeys($identifiers);
        $existing = ExamMark::where($lookupKeys)->first();

        if ($existing) {
            $existing->update($data);

            return $existing->fresh();
        }

        return ExamMark::create(array_merge($lookupKeys, $data));
    }
}
