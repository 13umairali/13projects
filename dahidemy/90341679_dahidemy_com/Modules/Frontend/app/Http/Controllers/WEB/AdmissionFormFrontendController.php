<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Student;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Services\GradeCalculationService;
use Modules\Frontend\Http\Requests\AdmissionForm\AdmissionFormStoreRequest;
use Modules\Frontend\Models\AcademicImage;
use Modules\Frontend\Models\AdmissionForm;
use App\Services\ApplicantNotificationService;

class AdmissionFormFrontendController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $gradeCalculationService,
        private readonly ApplicantNotificationService $notifier,
    ) {}

    public function index(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $classes = ClassModel::query()
            ->where('institute_id', optional($institute)->id)
            ->get();

        return view('themes.theme-one.pages.admission_form', [
            'classes' => $classes,
        ]);
    }

    public function store(AdmissionFormStoreRequest $request)
    {
        try {
            // ✅ validated data
            $data = $request->validated();

            $institute = InstituteHelper::resolveInstituteFromRequest($request);
            $branchId = $institute ? InstituteHelper::resolveBranchIdFromRequest($request) : null;

            $data['institute_id'] = optional($institute)->id;
            $data['branch_id'] = $branchId;

            // ✅ IMAGE UPLOAD
            if ($request->hasFile('image')) {
                $data['image'] = fileUploader('users/', 'png', $request->file('image'));
            }

            // ✅ STORE IN DATABASE
            $admission = AdmissionForm::create($data);

            // ✅ NOTIFY APPLICANT (best-effort — never blocks the submission)
            $this->notifySubmissionReceived($institute, $admission);

            // ✅ SUCCESS MESSAGE
            return redirect()
                ->route('admission-form-page')
                ->with('success', 'Admission submitted successfully!');
        } catch (Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Best-effort "we received your application" notification, sent via
     * whichever contact channels the applicant provided.
     */
    private function notifySubmissionReceived(?\Modules\Authentication\Models\Institute $institute, AdmissionForm $admission): void
    {
        if (! $institute) {
            return;
        }

        $name = trim($admission->first_name.' '.$admission->last_name);

        $this->notifier->notify(
            instituteId: $institute->id,
            branchId: $admission->branch_id,
            name: $name ?: 'Applicant',
            email: $admission->email,
            phone: $admission->phone,
            subject: 'Admission application received — '.$institute->name,
            lines: [
                "Thank you for applying to {$institute->name}. We've received your admission application and it is now pending review.",
                'We will notify you as soon as a decision has been made.',
            ],
            smsMessage: "Thank you for applying to {$institute->name}. Your admission application has been received and is pending review.",
            whatsappEvent: 'admission_submitted',
            whatsappParams: ['student_name' => $name, 'school_name' => $institute->name],
            referenceType: 'admission_form',
            referenceId: $admission->id,
            schoolName: $institute->name,
        );
    }

    public function resultPage(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $classes = ClassModel::query()
            ->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            })
            ->get();

        $student = null;
        $results = collect();
        $summary = [];

        // =========================
        // GLOBAL SEARCH TRIGGER
        // =========================
        if (
            $request->filled('class_id') ||
            $request->filled('roll_no') ||
            $request->filled('qr_code')
        ) {

            $studentQuery = Student::with([
                'studentSession.class',
                'studentSession.section',
                'studentSession.group',
            ])->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            });

            // =========================
            // QR CODE (HIGHEST PRIORITY)
            // =========================
            if ($request->filled('qr_code')) {

                $student = $studentQuery->whereHas('studentSession', function ($q) use ($request) {
                    $q->where('qr_code', $request->qr_code);
                })->first();
            } else {

                // =========================
                // NORMAL FILTER SEARCH
                // =========================
                $student = $studentQuery

                    ->when($request->class_id, function ($q) use ($request) {
                        $q->whereHas('studentSession', function ($sq) use ($request) {
                            $sq->where('class_id', $request->class_id);
                        });
                    })

                    ->when($request->roll_no, function ($q) use ($request) {
                        $q->whereHas('studentSession', function ($sq) use ($request) {
                            $sq->where('roll', $request->roll_no);
                        });
                    })

                    ->first();
            }

            // =========================
            // RESULT SECTION
            // =========================
            if ($student) {

                $results = ExamMark::with(['subject', 'exam'])
                    ->where('student_id', $student->id)
                    ->when($institute, function ($query) use ($institute): void {
                        $query->where('institute_id', $institute->id);
                    })
                    ->orderBy('subject_id')
                    ->get();

                $totalMarks = $results->sum('total_marks');
                $subjectCount = $results->count();
                $average = $subjectCount > 0 ? round($totalMarks / $subjectCount, 2) : 0;

                $grade = $this->gradeCalculationService->resolveGrade(
                    (int) ($student->studentSession->class_id ?? 0),
                    $average,
                    optional($institute)->id,
                );

                $gpa = $grade->grade_point ?? 0;
                $finalGrade = $grade->grade_name ?? 'F';

                $failed = $results->contains(function ($mark) {
                    return $mark->total_marks < 33;
                });

                $summary = [
                    'total_marks' => $totalMarks,
                    'average' => $average,
                    'grade' => $finalGrade,
                    'gpa' => $gpa,
                    'status' => $failed ? 'FAILED' : 'PASSED',
                    'subject_count' => $subjectCount,
                ];
            }
        }

        return view('themes.theme-one.pages.result_page', compact(
            'institute',
            'classes',
            'student',
            'results',
            'summary'
        ));
    }

    public function printResult($id)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest(request());
        $student = Student::query()
            ->with(['studentSession.class', 'studentSession.section', 'studentSession.group'])
            ->where('id', $id)
            ->where('institute_id', optional($institute)->id)
            ->firstOrFail();

        $results = ExamMark::with(['subject', 'exam'])
            ->where('student_id', $student->id)
            ->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            })
            ->get();

        $pdf = Pdf::loadView(
            'themes.theme-one.pages.result_pdf',
            compact('institute', 'student', 'results')
        )->setPaper('a4');

        return $pdf->download('result.pdf');
    }

    public function academicImages(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $academicImages = AcademicImage::query()
            ->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            })
            ->get();

        return view('themes.' . config('frontend.active_theme', 'theme-one') . '.pages.academic_images', [
            'academicImages' => $academicImages,
        ]);
    }
}
