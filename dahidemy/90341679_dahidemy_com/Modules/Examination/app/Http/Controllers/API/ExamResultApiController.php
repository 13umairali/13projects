<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Examination\Models\AssessmentDomain;
use Modules\Examination\Models\AssessmentItem;
use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Models\OnlineExamQuestion;
use Modules\Examination\Models\ResultCardSetting;
use Modules\Examination\Models\StudentAssessment;
use Modules\Examination\Services\EmptyMarkSheetService;
use Modules\Examination\Services\OnlineExamResultTransferService;
use Modules\Examination\Services\OnlineExamScoringService;
use Modules\Examination\Services\ResultCardService;
use Modules\Examination\Services\TabulationSheetService;

class ExamResultApiController extends Controller
{
    public function __construct(
        private readonly TabulationSheetService $tabulationSheetService,
        private readonly ResultCardService $resultCardService,
        private readonly EmptyMarkSheetService $emptyMarkSheetService,
        private readonly OnlineExamScoringService $scoringService,
        private readonly OnlineExamResultTransferService $transferService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function results(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',
        ]);

        $this->validateExamContext($request);

        $sheet = $this->tabulationSheetService->build(
            (int) $request->class_id,
            (int) $request->exam_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
        );

        $passCount = collect($sheet['rows'])->where('status', 'pass')->count();
        $failCount = collect($sheet['rows'])->where('status', 'fail')->count();

        return $this->responseSuccess([
            'summary' => [
                'total_students' => $sheet['student_count'],
                'passed' => $passCount,
                'failed' => $failCount,
            ],
            'subjects' => $sheet['subjects'],
            'data' => $sheet['rows'],
        ], 'Exam results fetched successfully.');
    }

    public function resultCard(Request $request): JsonResponse
    {
        $request->validate([
            'student_session_id' => 'required|exists:student_sessions,id',
            'exam_id' => 'required|exists:exams,id',
        ]);
        $session = $this->academicContext->assertOwnedStudentSession((int) $request->student_session_id);
        $session->load(['student.user', 'session', 'class', 'section', 'group']);

        return $this->responseSuccess(
            $this->resultCardService->singleTermCard($session, (int) $request->exam_id),
            'Result card fetched successfully.'
        );
    }

    public function annualResultCard(Request $request): JsonResponse
    {
        $request->validate([
            'student_session_id' => 'required|exists:student_sessions,id',
        ]);
        $session = $this->academicContext->assertOwnedStudentSession((int) $request->student_session_id);
        $session->load(['student.user', 'session', 'class', 'section', 'group']);

        return $this->responseSuccess(
            $this->resultCardService->annualCard($session),
            'Annual result card fetched successfully.'
        );
    }

    public function tabulation(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',
        ]);

        $this->validateExamContext($request);

        return $this->responseSuccess(
            $this->tabulationSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
                ! $request->boolean('with_results'),
            ),
            'Tabulation sheet generated successfully.'
        );
    }

    public function emptyMarkSheet(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'subject_ids' => 'nullable|array',
            'student_limit' => 'nullable|integer|min:1',
        ]);

        $this->validateExamContext($request);

        return $this->responseSuccess(
            $this->emptyMarkSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
                array_map('intval', (array) $request->input('subject_ids', [])),
                $request->filled('student_limit') ? (int) $request->student_limit : null,
            ),
            'Empty mark sheet generated successfully.'
        );
    }

    public function resultCardSettings(): JsonResponse
    {
        return $this->responseSuccess($this->resultCardService->settings(), 'Result card settings fetched.');
    }

    public function updateResultCardSettings(Request $request): JsonResponse
    {
        $settings = array_replace_recursive(ResultCardSetting::defaults(), $request->input('settings', []));
        $row = ResultCardSetting::updateOrCreate(
            [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'session_id' => get_option('academic_year') ?: null,
            ],
            [
                'result_title' => $request->input('result_title'),
                'settings' => $settings,
            ]
        );

        return $this->responseSuccess(['row' => $row, 'settings' => $row->mergedSettings()], 'Result card settings saved.');
    }

    public function assessmentDomains(): JsonResponse
    {
        return $this->responseSuccess(
            AssessmentDomain::with('items')->orderBy('display_order')->get(),
            'Assessment domains fetched.'
        );
    }

    public function storeAssessmentDomain(Request $request): JsonResponse
    {
        $data = $request->validate([
            'domain_type' => 'required|in:cognitive,affective,psychomotor',
            'name' => 'required|string|max:120',
            'display_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $domain = AssessmentDomain::create($data + [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'session_id' => get_option('academic_year') ?: null,
        ]);

        return $this->responseSuccess($domain, 'Assessment domain saved.');
    }

    public function storeAssessmentItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'assessment_domain_id' => 'required|exists:assessment_domains,id',
            'name' => 'required|string|max:150',
            'max_score' => 'required|numeric|min:0',
            'rating_type' => 'required|in:numeric,scale,grade',
            'grade_scale' => 'nullable|array',
            'display_order' => 'nullable|integer',
            'remarks_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
        $item = AssessmentItem::create($data + [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
        ]);

        return $this->responseSuccess($item, 'Assessment item saved.');
    }

    public function storeStudentAssessments(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'entries' => 'required|array',
        ]);

        $this->validateExamContext($request);

        foreach ($request->entries as $studentId => $items) {
            foreach ($items as $itemId => $payload) {
                StudentAssessment::updateOrCreate(
                    [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'session_id' => get_option('academic_year') ?: null,
                        'student_id' => (int) $studentId,
                        'class_id' => (int) $request->class_id,
                        'section_id' => $request->section_id,
                        'exam_id' => (int) $request->exam_id,
                        'assessment_item_id' => (int) $itemId,
                    ],
                    [
                        'score' => $payload['score'] ?? null,
                        'rating' => $payload['rating'] ?? null,
                        'remarks' => $payload['remarks'] ?? null,
                    ]
                );
            }
        }

        return $this->responseSuccess([], 'Domain assessments saved.');
    }

    public function onlineExams(): JsonResponse
    {
        return $this->responseSuccess(
            OnlineExam::with(['exam', 'class', 'subject'])->latest()->get(),
            'Online exams fetched.'
        );
    }

    public function storeOnlineExam(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'subject_id' => 'required|exists:subjects,id',
            'duration_minutes' => 'required|integer|min:1',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'pass_marks' => 'nullable|numeric',
            'attempt_limit' => 'required|integer|min:1',
            'negative_marking' => 'nullable|boolean',
            'negative_marks_per_wrong' => 'nullable|numeric',
            'random_questions' => 'nullable|boolean',
            'random_options' => 'nullable|boolean',
            'conversion_enabled' => 'nullable|boolean',
            'online_full_marks' => 'nullable|numeric',
            'academic_full_marks' => 'nullable|numeric',
            'transfer_policy' => 'required|in:skip,update,ask',
            'mark_component_slot' => 'nullable|integer|min:1|max:6',
            'is_active' => 'nullable|boolean',
        ]);

        $this->validateExamContext($request);

        $exam = OnlineExam::create($data + [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'session_id' => get_option('academic_year') ?: null,
        ]);

        return $this->responseSuccess($exam, 'Online exam created.');
    }

    public function storeOnlineExamQuestion(Request $request, int $id): JsonResponse
    {
        $onlineExam = OnlineExam::findOrFail($id);
        $data = $request->validate([
            'type' => 'required|in:multiple_choice,multiple_select,true_false,fill_blank,short_answer,descriptive',
            'question_text' => 'required|string',
            'options' => 'nullable|array',
            'correct_answer' => 'nullable|array',
            'explanation' => 'nullable|string',
            'marks' => 'required|numeric|min:0',
            'difficulty' => 'nullable|string',
        ]);
        $question = OnlineExamQuestion::create($data + [
            'institute_id' => $onlineExam->institute_id,
            'branch_id' => $onlineExam->branch_id,
            'online_exam_id' => $onlineExam->id,
            'display_order' => $onlineExam->questions()->count() + 1,
            'is_active' => true,
        ]);
        $onlineExam->update([
            'total_questions' => $onlineExam->questions()->count(),
            'total_marks' => $onlineExam->questions()->sum('marks'),
        ]);

        return $this->responseSuccess($question, 'Question added.');
    }

    public function transferPreview(Request $request, int $id): JsonResponse
    {
        $onlineExam = OnlineExam::findOrFail($id);

        return $this->responseSuccess(
            $this->transferService->preview($onlineExam, array_map('intval', (array) $request->input('student_ids', []))),
            'Transfer preview generated.'
        );
    }

    public function transferMarks(Request $request, int $id): JsonResponse
    {
        $onlineExam = OnlineExam::findOrFail($id);
        $result = $this->transferService->transfer(
            $onlineExam,
            array_map('intval', (array) $request->input('student_ids', [])),
            $request->input('policy'),
            auth()->id()
        );

        return $this->responseSuccess($result, 'Online marks transferred.');
    }

    public function studentOnlineExams(): JsonResponse
    {
        $student = Student::where('user_id', auth()->id())->with('studentSession')->first();
        if (! $student?->studentSession) {
            return $this->responseSuccess([], 'No student session found.');
        }
        $session = $student->studentSession;
        $exams = OnlineExam::with(['subject', 'exam'])
            ->where('is_active', true)
            ->where('class_id', $session->class_id)
            ->where(function ($query) use ($session) {
                $query->whereNull('section_id')->orWhere('section_id', $session->section_id);
            })
            ->get();

        return $this->responseSuccess($exams, 'Available online exams fetched.');
    }

    public function startStudentExam(int $id): JsonResponse
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        $onlineExam = OnlineExam::findOrFail($id);
        $attempt = $this->scoringService->startAttempt($onlineExam, (int) $student->id, auth()->id());

        return $this->responseSuccess([
            'attempt' => $attempt,
            'questions' => $this->scoringService->questionsForAttempt($onlineExam),
            'server_now' => now()->toIso8601String(),
            'expires_at' => optional($attempt->expires_at)->toIso8601String(),
        ], 'Exam attempt started.');
    }

    public function saveStudentAnswers(Request $request, int $attemptId): JsonResponse
    {
        $attempt = $this->ownedAttempt($attemptId);
        $attempt = $this->scoringService->saveAnswers($attempt, $request->input('answers', []));

        return $this->responseSuccess([
            'status' => $attempt->status,
            'expires_at' => optional($attempt->expires_at)->toIso8601String(),
            'server_now' => now()->toIso8601String(),
        ], 'Answers saved.');
    }

    public function submitStudentExam(Request $request, int $attemptId): JsonResponse
    {
        $attempt = $this->ownedAttempt($attemptId);
        $attempt = $this->scoringService->submit($attempt, $request->input('answers', $attempt->answers ?? []));

        return $this->responseSuccess($attempt, 'Exam submitted.');
    }

    private function ownedAttempt(int $attemptId): OnlineExamAttempt
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        $attempt = OnlineExamAttempt::with('onlineExam')->findOrFail($attemptId);
        abort_unless((int) $attempt->student_id === (int) $student->id, 403);

        return $attempt;
    }

    private function validateExamContext(Request $request): void
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
