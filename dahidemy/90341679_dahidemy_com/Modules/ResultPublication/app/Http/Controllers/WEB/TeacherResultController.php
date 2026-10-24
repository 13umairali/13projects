<?php

namespace Modules\ResultPublication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Subject;
use Modules\Examination\Models\OnlineExam;
use Modules\ResultPublication\Models\ResultPublication;
use RealRashid\SweetAlert\Facades\Alert;

class TeacherResultController extends Controller
{
    public function index()
    {
        $submissions = ResultPublication::where('institute_id', get_institute_id())
            ->where('submitted_by', auth()->id())
            ->with(['class', 'section', 'subject', 'exam'])
            ->latest()
            ->paginate(15);

        return view('resultpublication::teacher.index', compact('submissions'));
    }

    public function create()
    {
        return view('resultpublication::teacher.form', [
            'submission' => new ResultPublication(['type' => 'physical']),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('file')) {
            $validated['file'] = fileUploader('result_publications/', $request->file('file')->getClientOriginalExtension(), $request->file('file'));
        }

        ResultPublication::create([
            ...$validated,
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'submitted_by' => auth()->id(),
            'status' => ResultPublication::STATUS_PENDING,
        ]);

        Alert::success('Success', 'Result submitted for admin approval.');

        return redirect()->route('resultpublication.teacher.index');
    }

    public function edit(int $id)
    {
        $submission = ResultPublication::where('institute_id', get_institute_id())
            ->where('submitted_by', auth()->id())
            ->findOrFail($id);

        if (! $submission->isPending()) {
            Alert::error('Error', 'Only pending submissions can be edited. This one was already reviewed.');

            return redirect()->route('resultpublication.teacher.index');
        }

        return view('resultpublication::teacher.form', [
            'submission' => $submission,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $submission = ResultPublication::where('institute_id', get_institute_id())
            ->where('submitted_by', auth()->id())
            ->findOrFail($id);

        if (! $submission->isPending()) {
            Alert::error('Error', 'Only pending submissions can be edited.');

            return redirect()->route('resultpublication.teacher.index');
        }

        $validated = $this->validated($request);

        if ($request->hasFile('file')) {
            $validated['file'] = fileUploader('result_publications/', $request->file('file')->getClientOriginalExtension(), $request->file('file'), $submission->file);
        }

        $submission->update($validated);

        Alert::success('Success', 'Submission updated.');

        return redirect()->route('resultpublication.teacher.index');
    }

    public function destroy(int $id)
    {
        $submission = ResultPublication::where('institute_id', get_institute_id())
            ->where('submitted_by', auth()->id())
            ->findOrFail($id);

        if (! $submission->isPending()) {
            Alert::error('Error', 'Only pending submissions can be deleted.');

            return redirect()->route('resultpublication.teacher.index');
        }

        if ($submission->file) {
            fileRemover('result_publications/', $submission->file);
        }
        $submission->delete();

        Alert::success('Success', 'Submission deleted.');

        return redirect()->route('resultpublication.teacher.index');
    }

    private function formOptions(): array
    {
        return [
            'classes' => ClassModel::where('institute_id', get_institute_id())->orderBy('class_name')->get(),
            'sections' => Section::where('institute_id', get_institute_id())->orderBy('section_name')->get(),
            'subjects' => Subject::where('institute_id', get_institute_id())->orderBy('subject_name')->get(),
            'exams' => Exam::where('institute_id', get_institute_id())->orderByDesc('id')->get(),
            'onlineExams' => OnlineExam::where('institute_id', get_institute_id())->orderByDesc('id')->limit(100)->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'exam_id' => ['required', 'exists:exams,id'],
            'online_exam_id' => ['nullable', 'exists:online_exams,id'],
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:physical,online,summary'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        unset($data['file']); // handled separately

        return $data;
    }
}
