<?php

namespace Modules\Elearning\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\Enrollment;
use Modules\Elearning\Services\CourseEnrollmentService;

class CourseEnrollmentController extends Controller
{
    public function __construct(
        private readonly CourseEnrollmentService $enrollments,
    ) {}

    /**
     * Free course -> enroll immediately. Paid (one_time) course -> move on
     * to the gateway-selection step. Nothing is written to the database for
     * a paid course until a gateway is actually chosen, same reasoning as
     * the event-enrollment flow: avoids piling up abandoned rows.
     */
    public function enroll(Request $request, string $slug)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $course = Course::where('institute_id', $institute->id)->where('slug', $slug)->firstOrFail();

        if (! $this->enrollments->canSelfCheckout($course)) {
            return redirect()->route('courses-page.show', $course->slug)
                ->with('error', 'This course requires a subscription — please contact us directly to enroll.');
        }

        if ($this->enrollments->alreadyEnrolled($course, auth()->id())) {
            return redirect()->route('courses-page.show', $course->slug)
                ->with('error', "You're already enrolled in this course.");
        }

        if ($this->enrollments->isFree($course)) {
            $this->enrollments->enrollFree($course, auth()->id());

            return redirect()->route('courses-page.thankyou', $course->slug);
        }

        return redirect()->route('courses-page.checkout', $course->slug);
    }

    public function checkout(Request $request, string $slug)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $course = Course::where('institute_id', $institute->id)->where('slug', $slug)->firstOrFail();

        if ($this->enrollments->isFree($course) || ! $this->enrollments->canSelfCheckout($course)) {
            return redirect()->route('courses-page.show', $course->slug);
        }

        $gateways = $this->enrollments->availableGateways($institute->id);

        return view('elearning::public.checkout', [
            'institute' => $institute,
            'course' => $course,
            'price' => $this->enrollments->price($course),
            'gateways' => $gateways,
        ]);
    }

    public function pay(Request $request, string $slug, string $gateway)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $course = Course::where('institute_id', $institute->id)->where('slug', $slug)->firstOrFail();

        try {
            $redirectUrl = $this->enrollments->initiatePaidCheckout($course, auth()->id(), $gateway);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('courses-page.checkout', $course->slug)->with('error', $e->getMessage());
        }

        return redirect($redirectUrl);
    }

    public function thankYou(Request $request, string $slug)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $course = Course::where('institute_id', $institute->id)->where('slug', $slug)->firstOrFail();

        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('elearning::public.thank-you', compact('institute', 'course', 'enrollment'));
    }
}
