<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Exports\ExamResultExport;
use App\Http\Controllers\Controller;
use App\Imports\ExamImport;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Services\MarkEntryService;

class ExamMarkInputController extends Controller
{
    public function __construct(
        private readonly MarkEntryService $markEntryService,
    ) {}

    public function examResultView(): Factory|View
    {
        $sessionId = get_option('academic_year');
        $results = ExamMark::with('student', 'class', 'group', 'subject', 'exam')
            ->when($sessionId, fn ($q) => $q->where('session_id', $sessionId))
            ->limit(500)
            ->get();

        return view('examination::mark_config.mark_result_view', compact('results'));
    }

    public function excelFileDownload(Request $request)
    {
        return $request->all();
    }

    public function export(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class' => 'nullable|exists:classes,id',
            'group' => 'nullable|exists:student_groups,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'section' => 'nullable|exists:sections,id',
        ]);

        $examMarks = ExamMark::with(['student.studentSession', 'class', 'group', 'subject', 'exam'])
            ->where('exam_id', (int) $request->exam_id)
            ->when($request->filled('class'), fn ($query) => $query->where('class_id', (int) $request->class))
            ->when($request->filled('group'), fn ($query) => $query->where('group_id', (int) $request->group))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', (int) $request->subject_id))
            ->when($request->filled('section'), function ($query) use ($request) {
                $sectionId = (int) $request->section;
                $query->whereHas('student.studentSession', function ($sessionQuery) use ($sectionId) {
                    $sessionQuery->where('section_id', $sectionId);
                });
            })
            ->get();

        return Excel::download(new ExamResultExport($examMarks), 'results.csv');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
        ]);

        $import = new ExamImport;
        Excel::import($import, $request->file('file'));

        $identifierBase = [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'session_id' => get_option('academic_year'),
        ];

        try {
            DB::beginTransaction();

            foreach ($import->data as $row) {
                $identifiers = array_merge($identifierBase, [
                    'student_id' => $row['student_id'],
                    'class_id' => $row['class_id'],
                    'group_id' => $row['group_id'],
                    'subject_id' => $row['subject_id'],
                    'exam_id' => $row['exam_id'],
                ]);

                $markValuesByQuestion = [
                    1 => $row['mark_1'],
                    2 => $row['mark_2'],
                    3 => $row['mark_3'],
                    4 => $row['mark_4'],
                    5 => $row['mark_5'],
                    6 => $row['mark_6'],
                ];

                $this->markEntryService->saveSectionWiseMarks($identifiers, $markValuesByQuestion);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error occurred while importing file: '.$e->getMessage());
        }

        return back()->with('success', 'File imported successfully.');
    }
}
