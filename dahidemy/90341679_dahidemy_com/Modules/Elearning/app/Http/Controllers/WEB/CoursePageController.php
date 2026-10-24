<?php

namespace Modules\Elearning\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Services\CourseEnrollmentService;

class CoursePageController extends Controller
{
    public function __construct(
        private readonly CourseEnrollmentService $enrollments,
    ) {}

    public function index(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $courses = Course::where('institute_id', $institute->id)
            ->where('status', 'published')
            ->with('courseCategory')
            ->latest()
            ->paginate(12);

        return view('elearning::public.index', compact('institute', 'courses'));
    }

    public function show(Request $request, string $slug)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $course = Course::where('institute_id', $institute->id)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['courseFeatures', 'courseFaqs', 'courseCategory'])
            ->firstOrFail();

        $alreadyEnrolled = auth()->check() && $this->enrollments->alreadyEnrolled($course, auth()->id());

        return view('elearning::public.show', [
            'institute' => $institute,
            'course' => $course,
            'price' => $this->enrollments->price($course),
            'isFree' => $this->enrollments->isFree($course),
            'canSelfCheckout' => $this->enrollments->canSelfCheckout($course),
            'alreadyEnrolled' => $alreadyEnrolled,
        ]);
    }
}
