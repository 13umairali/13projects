<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Academic\Models\Exam;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\SectionService;
use Modules\Examination\Services\EmptyMarkSheetService;
use Modules\Examination\Services\ResultCalculationService;
use Modules\Examination\Services\ResultCardService;
use Modules\Examination\Services\TabulationSheetService;
use Pdf;

class ExamResultReportController extends Controller
{
    public function __construct(
        private readonly ClassService $classService,
        private readonly SectionService $sectionService,
        private readonly ResultCalculationService $resultCalculationService,
        private readonly ResultCardService $resultCardService,
        private readonly TabulationSheetService $tabulationSheetService,
        private readonly EmptyMarkSheetService $emptyMarkSheetService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function examResultView(Request $request): Factory|View
    {
        $this->authorizeResult();

        $results = collect();
        $exam = null;

        if ($request->filled('class_id') && $request->filled('exam_id')) {
            $this->validateExamFilters($request);
            $results = $this->tabulationSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
            );
            $exam = Exam::find((int) $request->exam_id);
        }

        return view('examination::results.index', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->scopedSections($request),
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'results' => $results,
            'exam' => $exam,
            'filters' => $request->only(['class_id', 'section_id', 'group_id', 'exam_id', 'search']),
        ]);
    }

    public function singleCard(Request $request): Factory|View
    {
        $this->authorizeResult();
        $payload = $this->singleCardPayload($request);

        return view('examination::results.single_card', $payload);
    }

    public function singleCardPdf(Request $request): Response
    {
        $this->authorizeResult();
        $payload = $this->singleCardPayload($request);
        $pdf = Pdf::loadView('examination::results.single_card_pdf', $payload)->setPaper('a4', 'portrait');

        return $pdf->download('result-card-'.now()->format('Y-m-d').'.pdf');
    }

    public function studentMarkView(Request $request): Factory|View
    {
        $this->authorizeResult();
        $payload = $this->singleCardPayload($request);

        return view('examination::results.student_marks', $payload);
    }

    public function studentMarkPdf(Request $request): Response
    {
        $this->authorizeResult();
        $payload = $this->singleCardPayload($request);
        $pdf = Pdf::loadView('examination::results.student_marks', $payload + ['hideToolbar' => true])->setPaper('a4', 'portrait');

        return $pdf->download('student-marks-'.now()->format('Y-m-d').'.pdf');
    }

    public function annualCard(Request $request): Factory|View
    {
        $this->authorizeResult();
        $payload = $this->annualCardPayload($request);

        return view('examination::results.annual_card', $payload);
    }

    public function annualCardPdf(Request $request): Response
    {
        $this->authorizeResult();
        $payload = $this->annualCardPayload($request);
        $pdf = Pdf::loadView('examination::results.annual_card', $payload + ['hideToolbar' => true])->setPaper('a4', 'portrait');

        return $pdf->download('annual-result-'.now()->format('Y-m-d').'.pdf');
    }

    public function tabulation(Request $request): Factory|View
    {
        $this->authorizeResult();

        $sheet = null;
        if ($request->filled('class_id') && $request->filled('exam_id')) {
            $this->validateExamFilters($request);
            $blankMarks = ! $request->boolean('with_results');
            $sheet = $this->tabulationSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
                $blankMarks,
            );
            $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;
            $sheet['header'] = $this->resultCardService->schoolHeader();
        }

        return view('examination::results.tabulation', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->scopedSections($request),
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'sheet' => $sheet,
            'filters' => $request->only(['class_id', 'section_id', 'group_id', 'exam_id', 'with_results']),
        ]);
    }

    public function tabulationPrint(Request $request): Factory|View
    {
        $this->authorizeResult();
        $sheet = $this->requireSheet($request);
        $sheet['header'] = $this->resultCardService->schoolHeader();
        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;

        return view('examination::results.tabulation_print', compact('sheet'));
    }

    public function tabulationPdf(Request $request): Response
    {
        $this->authorizeResult();
        $sheet = $this->requireSheet($request);
        $sheet['header'] = $this->resultCardService->schoolHeader();
        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;
        $paper = ($sheet['paper'] ?? 'a4') === 'a3' ? 'a3' : 'a4';
        $pdf = Pdf::loadView('examination::results.tabulation_print', compact('sheet'))
            ->setPaper($paper, 'landscape');

        return $pdf->download('tabulation-sheet-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * "Add Export to PDF/Excel for Exam Result... and Tabulation/Broad
     * Sheet." Renders the same already-correct print view straight into
     * Excel via FromView, rather than re-implementing the subject x
     * student matrix a second time in a hand-built export class (see
     * TabulationSheetExport's own comment for the full reasoning).
     */
    public function tabulationExcel(Request $request)
    {
        $this->authorizeResult();
        $sheet = $this->requireSheet($request);
        $sheet['header'] = $this->resultCardService->schoolHeader();
        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TabulationSheetExport($sheet),
            'tabulation-sheet-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * "Exam Result" export -- built from the exact same underlying sheet
     * data as Tabulation (TabulationSheetService::build()), so it reuses
     * the same print view/export rather than duplicating it.
     */
    public function examResultPdf(Request $request): Response
    {
        $this->authorizeResult();
        $this->validateExamFilters($request);
        $sheet = $this->tabulationSheetService->build(
            (int) $request->class_id,
            (int) $request->exam_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
        );
        $sheet['header'] = $this->resultCardService->schoolHeader();
        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;

        return Pdf::loadView('examination::results.tabulation_print', compact('sheet'))
            ->setPaper('a4', 'landscape')
            ->download('exam-result-'.now()->format('Y-m-d').'.pdf');
    }

    public function examResultExcel(Request $request)
    {
        $this->authorizeResult();
        $this->validateExamFilters($request);
        $sheet = $this->tabulationSheetService->build(
            (int) $request->class_id,
            (int) $request->exam_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
        );
        $sheet['header'] = $this->resultCardService->schoolHeader();
        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TabulationSheetExport($sheet),
            'exam-result-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function emptyMarkSheet(Request $request): Factory|View
    {
        $this->authorizeMarkInput();

        $sheet = null;
        if ($request->filled('class_id') && $request->filled('exam_id')) {
            $this->validateExamFilters($request);
            $subjectIds = array_filter(array_map('intval', (array) $request->input('subject_ids', [])));
            $sheet = $this->emptyMarkSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
                $subjectIds,
                $request->filled('student_limit') ? (int) $request->student_limit : null,
            );
            $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;
        }

        return view('examination::results.empty_mark_sheet', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->scopedSections($request),
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'sheet' => $sheet,
            'filters' => $request->all(),
        ]);
    }

    public function emptyMarkSheetPrint(Request $request): Factory|View
    {
        $this->authorizeMarkInput();
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
        ]);

        $this->validateExamFilters($request);

        $subjectIds = array_filter(array_map('intval', (array) $request->input('subject_ids', [])));
        $sheet = $this->emptyMarkSheetService->build(
            (int) $request->class_id,
            (int) $request->exam_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
            $subjectIds,
            $request->filled('student_limit') ? (int) $request->student_limit : null,
        );
        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;

        return view('examination::results.empty_mark_sheet_print', compact('sheet'));
    }

    private function singleCardPayload(Request $request): array
    {
        $request->validate([
            'student_session_id' => 'required|exists:student_sessions,id',
            'exam_id' => 'required|exists:exams,id',
        ]);

        $session = $this->academicContext->assertOwnedStudentSession((int) $request->student_session_id);
        $session->load(['student.user', 'session', 'class', 'section', 'group']);

        return $this->resultCardService->singleTermCard($session, (int) $request->exam_id);
    }

    private function annualCardPayload(Request $request): array
    {
        $request->validate([
            'student_session_id' => 'required|exists:student_sessions,id',
        ]);

        $session = $this->academicContext->assertOwnedStudentSession((int) $request->student_session_id);
        $session->load(['student.user', 'session', 'class', 'section', 'group']);

        return $this->resultCardService->annualCard($session);
    }

    private function requireSheet(Request $request): array
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
        ]);

        $this->validateExamFilters($request);

        $blankMarks = ! $request->boolean('with_results');

        return $this->tabulationSheetService->build(
            (int) $request->class_id,
            (int) $request->exam_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
            $blankMarks,
        );
    }

    private function authorizeResult(): void
    {
        abort_unless(auth()->user()?->can('exam_module_exam_result') || auth()->user()?->can('exam_module_print_result') || auth()->user()?->can('exam_module_view_broad_sheet'), 403);
    }

    private function authorizeMarkInput(): void
    {
        abort_unless(auth()->user()?->can('exam_module_mark_input'), 403);
    }

    private function scopedSections(Request $request)
    {
        if (! $request->filled('class_id')) {
            return collect();
        }

        return $this->academicContext->sectionsForClass((int) $request->class_id);
    }

    private function validateExamFilters(Request $request): void
    {
        if (! $request->filled('class_id')) {
            return;
        }

        $this->academicContext->assertClassSectionGroup(
            (int) $request->class_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
        );
    }
}
