<?php

namespace App\Http\Controllers;

use App\Helpers\InstituteHelper;
use Illuminate\Http\Request;
use Modules\Academic\Models\Event;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Signature;
use Modules\Academic\Models\Staff;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\Subject;
use Modules\Academic\Models\Teacher;
use Modules\Academic\Services\StudentService;
use Modules\Blog\Models\BlogPost;
use Modules\Elearning\Models\Course;
use Modules\Examination\Models\ClassExam;
use Modules\Examination\Models\OnlineExam;
use Modules\FormBuilder\Models\DynamicForm;
use Modules\Frontend\Models\AboutUs;
use Modules\Frontend\Models\AcademicImage;
use Modules\Frontend\Models\Banner;
use Modules\Frontend\Models\FaqQuestion;
use Modules\Frontend\Models\MobileAppSection;
use Modules\Frontend\Models\OurHistory;
use Modules\Frontend\Models\Page;
use Modules\Frontend\Models\ReadyToJoinUs;
use Modules\Frontend\Models\Testimonial;
use Modules\Frontend\Models\WhyChooseUs;
use Modules\HomepageBuilder\Models\DirectorMessage;
use Modules\HomepageBuilder\Models\HomepageSection;
use Modules\ResultPublication\Models\ResultPublication;
use Modules\SystemConfiguration\Models\Setting;
use RealRashid\SweetAlert\Facades\Alert;

class WebsiteController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService,
    ) {}

    public function index(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | Detect Domain
    |--------------------------------------------------------------------------
    */

        $host = $request->getHost();
        /*
    |--------------------------------------------------------------------------
    | Find Institute
    |--------------------------------------------------------------------------
    */
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        if (! $institute) {
            abort(404, 'Institute not found');
        }

        $instituteId = $institute->id;

        /*
    |--------------------------------------------------------------------------
    | Cache Homepage Data
    |--------------------------------------------------------------------------
    */

        $data =
            [
                'banners' => Banner::where('institute_id', $instituteId)
                    ->latest()
                    ->get(),

                'about' => AboutUs::where('institute_id', $instituteId)
                    ->where('status', 1)
                    ->first(),

                'academicImages' => AcademicImage::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'whyChooseUs' => WhyChooseUs::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'readyToJoinUs' => ReadyToJoinUs::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'teachers' => Teacher::with('user')
                    ->where('institute_id', $instituteId)
                    ->limit(4)
                    ->get(),

                'staffs' => Staff::with('user')
                    ->where('institute_id', $instituteId)
                    ->limit(4)
                    ->get(),

                'events' => Event::where('institute_id', $instituteId)
                    ->where('is_public', true)
                    ->latest()
                    ->limit(5)
                    ->get(),

                'notices' => Notice::where('institute_id', $instituteId)
                    ->where('display_on_website', true)
                    ->latest()
                    ->limit(5)
                    ->get(),

                'faqs' => FaqQuestion::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'history' => OurHistory::where(
                    'institute_id',
                    $instituteId
                )->first(),

                'testimonials' => Testimonial::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'mobileAppSections' => MobileAppSection::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'courses' => Course::where(
                    'institute_id',
                    $instituteId
                )
                    ->limit(6)
                    ->get(),

                // Dynamic Form Builder — forms an admin has explicitly opted to
                // feature on the homepage. Empty collection (and the section
                // renders nothing) when the module isn't installed or no form
                // has "show_on_homepage" enabled, so this is safe to add either way.
                'dynamicForms' => class_exists(DynamicForm::class)
                    ? DynamicForm::where('institute_id', $instituteId)
                        ->published()
                        ->onHomepage()
                        ->latest()
                        ->limit(3)
                        ->get()
                    : collect(),

                // Upcoming online exams — a lightweight "Exams" teaser for the
                // homepage. Safe no-op if the anti-cheating/Examination patch
                // hasn't been applied to this installation.
                'upcomingExams' => class_exists(OnlineExam::class)
                    ? OnlineExam::where('institute_id', $instituteId)
                        ->where('is_active', true)
                        ->where(function ($query) {
                            $query->whereNull('end_at')->orWhere('end_at', '>=', now());
                        })
                        ->with(['class', 'section', 'subject'])
                        ->orderBy('start_at')
                        ->limit(4)
                        ->get()
                    : collect(),

                // Recently published results — an announcement-style teaser
                // only (never actual marks, for privacy). Safe no-op if the
                // ResultPublication module isn't installed.
                'resultAnnouncements' => class_exists(ResultPublication::class)
                    ? ResultPublication::where('institute_id', $instituteId)
                        ->where('status', ResultPublication::STATUS_APPROVED)
                        ->with(['class', 'section', 'subject', 'exam'])
                        ->orderByDesc('published_at')
                        ->limit(4)
                        ->get()
                    : collect(),

                // Admin-controlled homepage section order (Modules\HomepageBuilder).
                // Empty array (and the template falls back to its fixed default
                // order) when the module isn't installed.
                'homepageSectionOrder' => class_exists(HomepageSection::class)
                    ? HomepageSection::enabledViewsForInstitute($instituteId, null)
                    : [],

                'directorMessage' => class_exists(DirectorMessage::class)
                    ? DirectorMessage::where('institute_id', $instituteId)->first()
                    : null,

                'latestBlogPosts' => class_exists(BlogPost::class)
                    ? BlogPost::where('institute_id', $instituteId)
                        ->published()
                        ->orderByDesc('published_at')
                        ->limit(3)
                        ->get()
                    : collect(),
            ];

        return view(
            'themes.' . config('frontend.active_theme', 'theme-one') . '.pages.index',
            compact(
                'institute',
                'data'
            )
        );
    }

    public function show(Request $request, $slug)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);

        $page = Page::where('slug', $slug)
            ->where('status', 1)
            ->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            })
            ->firstOrFail();

        return view('themes.' . config('frontend.active_theme', 'theme-one') . '.pages.page', compact('page'));
    }

    /**
     * Build a signed token for the public /student-vital/{encodedData} route. The signature
     * prevents a visitor from forging or altering institute_id/branch_id/student_id to view
     * another tenant's student — see vital() below.
     */
    public static function buildStudentVitalToken(int $instituteId, int $branchId, int $studentId): string
    {
        $payload = [
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'student_id' => $studentId,
        ];
        $payload['sig'] = hash_hmac('sha256', "{$instituteId}|{$branchId}|{$studentId}", config('app.key'));

        return base64_encode(json_encode($payload));
    }

    public function vital(string $encodedData)
    {
        try {
            // Decode
            $decoded = base64_decode($encodedData, true);

            if ($decoded === false) {
                return abort(404, 'Invalid request.');
            }

            $data = json_decode($decoded, true);

            // Validate structure
            if (
                ! is_array($data) ||
                ! isset($data['institute_id'], $data['branch_id'], $data['student_id'], $data['sig']) ||
                ! ctype_digit((string) $data['institute_id']) ||
                ! ctype_digit((string) $data['branch_id']) ||
                ! ctype_digit((string) $data['student_id'])
            ) {
                return abort(404, 'Invalid data format.');
            }

            $instituteId = (int) $data['institute_id'];
            $branchId = (int) $data['branch_id'];
            $studentId = (int) $data['student_id'];

            // Reject any payload whose signature doesn't match — prevents visitors from
            // forging institute_id/branch_id/student_id to view another tenant's student.
            $expectedSig = hash_hmac('sha256', "{$instituteId}|{$branchId}|{$studentId}", config('app.key'));
            if (! hash_equals($expectedSig, (string) $data['sig'])) {
                return abort(404, 'Invalid or expired link.');
            }

            // Look up the student scoped to the signed institute/branch — never via an
            // unscoped repository lookup, since this route has no authenticated tenant context.
            $student = Student::where('id', $studentId)
                ->where('institute_id', $instituteId)
                ->where('branch_id', $branchId)
                ->first();

            if (! $student) {
                return abort(404, 'Student not found.');
            }

            $exams = ClassExam::with('exam')
                ->where('class_id', $student->studentSession?->class_id)
                ->get()
                ->pluck('exam');

            $subjects = Subject::where('class_id', $student->studentSession?->class_id)->get();

            return view('vital.index', compact('student', 'exams', 'subjects', 'studentId', 'instituteId', 'branchId'));
        } catch (\Throwable $e) {
            return abort(404, 'Something went wrong.');
        }
    }

    public function studentIdCardPage()
    {
        return view('backend.students.id-card.index');
    }

    public function teacherIdCardPage()
    {
        // Extract IDs
        $instituteId = (int) get_institute_id();
        $branchId = (int) get_branch_id();

        // Fetch teachers
        $teachers = Teacher::select(
            'teachers.*',
            'teachers.id AS id',
            'teachers.status AS teacher_status',
            'departments.department_name',
            'users.image as user_image',
            'users.name as user_name',
        )
            ->where('teachers.institute_id', $instituteId)
            ->where('teachers.branch_id', $branchId)
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'teachers.department_id')
            ->orderBy('teachers.id', 'DESC')
            ->get();

        // Settings & signature
        $principalSignature = Signature::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->first();
        $settings = Setting::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->pluck('value', 'name')
            ->toArray();

        return view('backend.teachers.id-card.card-list', compact(
            'teachers',
            'principalSignature',
            'instituteId',
            'branchId',
            'settings'
        ));
    }

    public function staffIdCardPage()
    {
        // Extract IDs
        $instituteId = (int) get_institute_id();
        $branchId = (int) get_branch_id();

        // Fetch staffs
        $staffs = Staff::select(
            'staffs.*',
            'staffs.id AS id',
            'departments.department_name',
            'users.image as user_image',
            'users.name as user_name',
        )
            ->where('staffs.institute_id', $instituteId)
            ->where('staffs.branch_id', $branchId)
            ->join('users', 'users.id', '=', 'staffs.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'staffs.department_id')
            ->get();

        // Settings & signature
        $principalSignature = Signature::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->first();
        $settings = Setting::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->pluck('value', 'name')
            ->toArray();

        return view('backend.staffs.id-card.card-list', compact(
            'staffs',
            'principalSignature',
            'instituteId',
            'branchId',
            'settings'
        ));
    }

    public function studentIdCardList(string $encodedData)
    {
        try {
            // Decode Base64
            $decoded = base64_decode($encodedData, true);

            if ($decoded === false) {
                return abort(404, 'Invalid request.');
            }

            $data = json_decode($decoded, true);

            if (! is_array($data)) {
                return abort(404, 'Invalid data format.');
            }

            // Extract with defaults
            $class_id = (int) $data['class_id'];
            $section_id = (int) $data['section_id'];
            $group_id = (int) $data['group_id'];
            $validity_date = $data['validity'];
            $instituteId = (int) get_institute_id();
            $branchId = (int) get_branch_id();

            // Fetch students
            $students = $this->studentService->getStudentsByClassSectionGroup(
                $class_id,
                $section_id,
                $group_id,
                200,
                $instituteId,
                $branchId
            );

            // Fetch settings
            $settings = Setting::where('institute_id', $instituteId)
                ->where('branch_id', $branchId)
                ->pluck('value', 'name')
                ->toArray();

            $principalSignature = Signature::where('institute_id', $instituteId)
                ->where('branch_id', $branchId)
                ->first();

            return view('backend.students.id-card.card-list', compact(
                'students',
                'validity_date',
                'principalSignature',
                'instituteId',
                'branchId',
                'settings'
            ));
        } catch (\Throwable $e) {
            return abort(404, 'Something went wrong.');
        }
    }

    public function instituteCacheClear()
    {
        clearInstituteHomepageCache(get_institute_id());

        Alert::toast('Institute Cache clear successful!', 'success');

        return redirect('dashboard');
    }
}
