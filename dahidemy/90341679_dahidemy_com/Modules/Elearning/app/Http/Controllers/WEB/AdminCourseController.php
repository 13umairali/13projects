<?php

namespace Modules\Elearning\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\CourseCategory;
use RealRashid\SweetAlert\Facades\Alert;

class AdminCourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('institute_id', get_institute_id())
            ->with('courseCategory')
            ->latest()
            ->paginate(15);

        return view('elearning::admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('elearning::admin.courses.form', [
            'course' => new Course(['payment_type' => 'free', 'type' => 'single', 'status' => 'draft']),
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(5);

        if ($request->hasFile('image')) {
            $validated['image'] = fileUploader('courses/', $request->file('image')->getClientOriginalExtension(), $request->file('image'));
        }

        $course = Course::create([
            ...$validated,
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'created_by' => auth()->id(),
        ]);

        Alert::success('Success', 'Course created — now add chapters, lessons, features, and FAQs below.');

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    public function edit(int $id)
    {
        $course = Course::where('institute_id', get_institute_id())->findOrFail($id);

        return view('elearning::admin.courses.form', [
            'course' => $course,
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $course = Course::where('institute_id', get_institute_id())->findOrFail($id);
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image'] = fileUploader('courses/', $request->file('image')->getClientOriginalExtension(), $request->file('image'), $course->image);
        }

        $course->update($validated);

        Alert::success('Success', 'Course updated.');

        return redirect()->route('elearning.admin.courses.index');
    }

    public function destroy(int $id)
    {
        $course = Course::where('institute_id', get_institute_id())->findOrFail($id);

        if ($course->enrollments()->exists()) {
            Alert::error('Error', 'This course has enrollments — it can\'t be deleted. Unpublish it instead by setting status to Draft.');

            return back();
        }

        $course->delete();

        Alert::success('Success', 'Course deleted.');

        return redirect()->route('elearning.admin.courses.index');
    }

    private function categories()
    {
        return CourseCategory::where('institute_id', get_institute_id())->orderBy('name')->get();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'course_category_id' => ['required', 'exists:course_categories,id'],
            'type' => ['required', 'in:single,bundle'],
            'payment_type' => ['required', 'in:free,one_time,recurring_payment'],
            'regular_price' => ['nullable', 'numeric', 'min:0', 'required_if:payment_type,one_time,recurring_payment'],
            'offer_price' => ['nullable', 'numeric', 'min:0', 'lte:regular_price'],
            'payment_duration' => ['nullable', 'string', 'max:50'],
            'total_cycles' => ['nullable', 'integer', 'min:1'],
            'is_infinity' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'video_type' => ['nullable', 'in:none,youtube,vimeo,upload,external,embed'],
            'video_url' => ['nullable', 'string', 'max:1000'],
            'embedded_url' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:draft,schedule,published,private'],
            'publish_date' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        unset($data['image']); // handled separately
        $data['is_infinity'] = $request->boolean('is_infinity');

        // The database column defaults both prices to 0.00 — but that
        // default only applies when the column is left out of the INSERT
        // entirely. Explicitly sending null (which validation allows for a
        // Free course) still hits the NOT NULL constraint, so a Free course
        // gets an explicit 0 here instead of null.
        if ($data['payment_type'] === 'free') {
            $data['regular_price'] = 0;
            $data['offer_price'] = 0;
        } else {
            $data['offer_price'] = $data['offer_price'] ?? 0;
        }

        return $data;
    }
}
