<?php

namespace Modules\Elearning\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Elearning\Models\Chapter;
use Modules\Elearning\Models\Content;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\CourseFaq;
use Modules\Elearning\Models\CourseFeature;
use Modules\Elearning\Models\Lesson;
use RealRashid\SweetAlert\Facades\Alert;

class AdminCourseBuilderController extends Controller
{
    public function show(int $courseId)
    {
        $course = Course::where('institute_id', get_institute_id())
            ->with([
                'courseFeatures' => fn ($q) => $q->orderBy('priority'),
                'courseFaqs',
                'courseChapters.lessons',
            ])
            ->findOrFail($courseId);

        return view('elearning::admin.courses.builder', compact('course'));
    }

    // ===================== CHAPTERS =====================

    public function storeChapter(Request $request, int $courseId)
    {
        $course = $this->ownedCourse($courseId);

        $data = $request->validate(['title' => ['required', 'string', 'max:191']]);

        Chapter::create([
            'institute_id' => get_institute_id(),
            'course_id' => $course->id,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'priority' => (Chapter::where('course_id', $course->id)->max('priority') ?? 0) + 1,
            'status' => 1,
            'created_by' => auth()->id(),
        ]);

        Alert::success('Success', 'Chapter added.');

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    public function updateChapter(Request $request, int $courseId, int $chapterId)
    {
        $course = $this->ownedCourse($courseId);
        $chapter = Chapter::where('course_id', $course->id)->findOrFail($chapterId);

        $data = $request->validate(['title' => ['required', 'string', 'max:191']]);
        $chapter->update(['title' => $data['title']]);

        Alert::success('Success', 'Chapter updated.');

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    public function destroyChapter(int $courseId, int $chapterId)
    {
        $course = $this->ownedCourse($courseId);
        $chapter = Chapter::where('course_id', $course->id)->findOrFail($chapterId);

        Content::where('chapter_id', $chapter->id)->delete();
        Lesson::where('chapter_id', $chapter->id)->delete();
        $chapter->delete();

        Alert::success('Success', 'Chapter and its lessons deleted.');

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    // ===================== LESSONS =====================

    public function storeLesson(Request $request, int $courseId, int $chapterId)
    {
        $course = $this->ownedCourse($courseId);
        $chapter = Chapter::where('course_id', $course->id)->findOrFail($chapterId);

        $data = $this->validatedLesson($request);

        $lesson = Lesson::create([
            ...$data,
            'institute_id' => get_institute_id(),
            'course_id' => $course->id,
            'chapter_id' => $chapter->id,
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'priority' => (Lesson::where('chapter_id', $chapter->id)->max('priority') ?? 0) + 1,
            'status' => 1,
            'created_by' => auth()->id(),
        ]);

        // A Lesson only shows up in the course's content list once there's a
        // matching Content row pointing at it — Course::courseChapters()
        // reads from `contents`, not `lessons`, directly.
        Content::create([
            'institute_id' => get_institute_id(),
            'chapter_id' => $chapter->id,
            'type' => 'lesson',
            'type_id' => $lesson->id,
            'serial' => Content::nextSerial($chapter->id),
        ]);

        Alert::success('Success', 'Lesson added.');

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    public function updateLesson(Request $request, int $courseId, int $lessonId)
    {
        $course = $this->ownedCourse($courseId);
        $lesson = Lesson::where('course_id', $course->id)->findOrFail($lessonId);

        $lesson->update($this->validatedLesson($request));

        Alert::success('Success', 'Lesson updated.');

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    public function destroyLesson(int $courseId, int $lessonId)
    {
        $course = $this->ownedCourse($courseId);
        $lesson = Lesson::where('course_id', $course->id)->findOrFail($lessonId);

        Content::where('type', 'lesson')->where('type_id', $lesson->id)->delete();
        $lesson->delete();

        Alert::success('Success', 'Lesson deleted.');

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    // ===================== FEATURES =====================

    public function storeFeature(Request $request, int $courseId)
    {
        $course = $this->ownedCourse($courseId);
        $data = $request->validate(['name' => ['required', 'string', 'max:191']]);

        CourseFeature::create([
            'institute_id' => get_institute_id(),
            'course_id' => $course->id,
            'name' => $data['name'],
            'priority' => (CourseFeature::where('course_id', $course->id)->max('priority') ?? 0) + 1,
            'status' => 1,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    public function destroyFeature(int $courseId, int $featureId)
    {
        $course = $this->ownedCourse($courseId);
        CourseFeature::where('course_id', $course->id)->findOrFail($featureId)->delete();

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    // ===================== FAQs =====================

    public function storeFaq(Request $request, int $courseId)
    {
        $course = $this->ownedCourse($courseId);
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
        ]);

        CourseFaq::create([
            ...$data,
            'institute_id' => get_institute_id(),
            'course_id' => $course->id,
            'status' => 1,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    public function destroyFaq(int $courseId, int $faqId)
    {
        $course = $this->ownedCourse($courseId);
        CourseFaq::where('course_id', $course->id)->findOrFail($faqId)->delete();

        return redirect()->route('elearning.admin.courses.builder', $course->id);
    }

    private function ownedCourse(int $courseId): Course
    {
        return Course::where('institute_id', get_institute_id())->findOrFail($courseId);
    }

    private function validatedLesson(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'video_type' => ['required', 'in:none,youtube,vimeo,upload,external,embed'],
            'video_url' => ['nullable', 'string', 'max:1000'],
            'embedded_url' => ['nullable', 'string', 'max:1000'],
        ]);

        return $data;
    }
}
