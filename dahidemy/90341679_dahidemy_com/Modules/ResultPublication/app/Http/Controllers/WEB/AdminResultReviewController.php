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
use Modules\ResultPublication\Services\ResultNotificationService;
use RealRashid\SweetAlert\Facades\Alert;

class AdminResultReviewController extends Controller
{
    public function __construct(
        private readonly ResultNotificationService $notifier,
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $statusMap = [
            'pending' => ResultPublication::STATUS_PENDING,
            'approved' => ResultPublication::STATUS_APPROVED,
            'rejected' => ResultPublication::STATUS_REJECTED,
        ];

        $submissions = ResultPublication::where('institute_id', get_institute_id())
            ->when(isset($statusMap[$status]), fn ($q) => $q->where('status', $statusMap[$status]))
            ->with(['class', 'section', 'subject', 'exam'])
            ->latest()
            ->paginate(15)
            ->appends(['status' => $status]);

        return view('resultpublication::admin.index', compact('submissions', 'status'));
    }

    /**
     * Admin creating a result directly — independent of any teacher
     * submission. Same form fields as the teacher's, plus the option to
     * publish (and notify) immediately instead of landing as Pending.
     */
    public function create()
    {
        return view('resultpublication::admin.form', [
            'submission' => new ResultPublication(['type' => 'physical']),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $publishNow = $request->boolean('publish_immediately');

        if ($request->hasFile('file')) {
            $validated['file'] = fileUploader('result_publications/', $request->file('file')->getClientOriginalExtension(), $request->file('file'));
        }

        $submission = ResultPublication::create([
            ...$validated,
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'submitted_by' => auth()->id(),
            'status' => $publishNow ? ResultPublication::STATUS_APPROVED : ResultPublication::STATUS_PENDING,
            'reviewed_by' => $publishNow ? auth()->id() : null,
            'reviewed_at' => $publishNow ? now() : null,
            'published_at' => $publishNow ? now() : null,
        ]);

        if ($publishNow) {
            $count = $this->notifier->notifyClassSection($submission);
            $submission->update(['notified_at' => now()]);
            Alert::success('Success', "Result created and published. {$count} student/parent contacts notified.");
        } else {
            Alert::success('Success', 'Result created as a pending submission.');
        }

        return redirect()->route('resultpublication.admin.index');
    }

    /**
     * Unlike the teacher's own edit (which only allows changes while
     * Pending), admin can edit a submission in any status — including
     * fixing a typo on something already published.
     */
    public function edit(int $id)
    {
        $submission = ResultPublication::where('institute_id', get_institute_id())->findOrFail($id);

        return view('resultpublication::admin.form', [
            'submission' => $submission,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $submission = ResultPublication::where('institute_id', get_institute_id())->findOrFail($id);
        $validated = $this->validated($request);

        if ($request->hasFile('file')) {
            $validated['file'] = fileUploader('result_publications/', $request->file('file')->getClientOriginalExtension(), $request->file('file'), $submission->file);
        }

        // 'status' is deliberately NOT part of the shared validated() helper —
        // that method is also used by the teacher-facing controller, which
        // must never be able to set its own approval status. Admin-only,
        // handled here explicitly instead.
        $requestedStatus = $request->filled('status') ? (int) $request->input('status') : null;
        if ($requestedStatus && in_array($requestedStatus, [
            ResultPublication::STATUS_PENDING,
            ResultPublication::STATUS_APPROVED,
            ResultPublication::STATUS_REJECTED,
        ], true)) {
            $validated['status'] = $requestedStatus;
        }

        $wasApprovedBefore = $submission->isApproved();
        $submission->update($validated);

        // Editing content never re-triggers a notification on its own —
        // only a Pending/Rejected -> Approved transition does, and only once
        // (notified_at guards against a second send on a later edit).
        if (! $wasApprovedBefore && $submission->isApproved() && ! $submission->notified_at) {
            $submission->update(['published_at' => $submission->published_at ?: now(), 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
            $count = $this->notifier->notifyClassSection($submission);
            $submission->update(['notified_at' => now()]);
            Alert::success('Success', "Result updated and published. {$count} student/parent contacts notified.");
        } else {
            Alert::success('Success', 'Result updated.');
        }

        return redirect()->route('resultpublication.admin.index');
    }

    public function destroy(int $id)
    {
        $submission = ResultPublication::where('institute_id', get_institute_id())->findOrFail($id);

        if ($submission->file) {
            fileRemover('result_publications/', $submission->file);
        }
        $submission->delete();

        Alert::success('Success', 'Submission deleted.');

        return redirect()->route('resultpublication.admin.index');
    }

    public function show(int $id)
    {
        $submission = ResultPublication::where('institute_id', get_institute_id())
            ->with(['class', 'section', 'subject', 'exam', 'onlineExam'])
            ->findOrFail($id);

        return view('resultpublication::admin.show', compact('submission'));
    }

    public function approve(Request $request, int $id)
    {
        $submission = ResultPublication::where('institute_id', get_institute_id())->findOrFail($id);

        if (! $submission->isPending()) {
            Alert::error('Error', 'This submission was already reviewed.');

            return back();
        }

        $submission->update([
            'status' => ResultPublication::STATUS_APPROVED,
            'remarks' => $request->input('remarks'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'published_at' => now(),
        ]);

        // Guarded so re-visiting/re-saving this record never double-sends.
        if (! $submission->notified_at) {
            $count = $this->notifier->notifyClassSection($submission);
            $submission->update(['notified_at' => now()]);
            Alert::success('Success', "Result approved and published. {$count} student/parent contacts notified.");
        } else {
            Alert::success('Success', 'Result approved and published.');
        }

        return redirect()->route('resultpublication.admin.index');
    }

    public function reject(Request $request, int $id)
    {
        $submission = ResultPublication::where('institute_id', get_institute_id())->findOrFail($id);

        if (! $submission->isPending()) {
            Alert::error('Error', 'This submission was already reviewed.');

            return back();
        }

        $submission->update([
            'status' => ResultPublication::STATUS_REJECTED,
            'remarks' => $request->input('remarks'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Alert::success('Success', 'Submission rejected — the teacher can see your remarks and resubmit.');

        return redirect()->route('resultpublication.admin.index');
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
