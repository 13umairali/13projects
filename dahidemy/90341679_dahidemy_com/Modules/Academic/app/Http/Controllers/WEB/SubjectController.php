<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Subject;
use RealRashid\SweetAlert\Facades\Alert;

class SubjectController extends Controller
{
    public function index($class = ''): Factory|View
    {
        $subjects = Subject::select('*', 'subjects.id AS id')
            ->join('classes', 'classes.id', '=', 'subjects.class_id')
            ->whereNull('classes.deleted_at')
            ->where('subjects.institute_id', get_institute_id())
            ->where('subjects.branch_id', get_branch_id())
            ->where('subjects.class_id', $class)
            ->orderBy('subjects.id', 'DESC')
            ->get();

        return view('academic::subjects.subject-list', compact('subjects', 'class'));
    }

    public function create(): Factory|View
    {
        return view('academic::subjects.subject-add');
    }

    public function store(Request $request): RedirectResponse
    {
        // subject_name and subject_code are NOT NULL in the database
        // (varchar(40) and varchar(20) respectively). The old validation
        // allowed blank/long values here, which passed Laravel's checks but
        // then failed at the database with a raw SQL error — which is why
        // "Added Subjects cannot be saved" for any code-less or long-code
        // entry. Validation below now matches the real column constraints,
        // so the user gets a clear message instead of a crash.
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'nullable|numeric',
            'subject_name' => 'required|string|max:40',
            'subject_code' => 'required|string|max:20',
            'subject_short_form' => 'nullable|string|max:191',
            'subject_type' => 'nullable|string|max:191',
            'subject_type_form' => 'nullable|string|max:191',
            'sl_no' => 'nullable|integer|min:0',
        ]);

        // Course Grouping: some classes (e.g. Kindergarten, Nursery/Prep)
        // need a hard cap on how many distinct Student Groups they can be
        // paired with — student_groups is institute-wide with no class_id
        // of its own, so without this check any class could accumulate an
        // unlimited number of unrelated groups over time.
        if ($request->group_id) {
            $class = ClassModel::find($request->class_id);
            if ($class && $class->wouldExceedGroupLimit((int) $request->group_id)) {
                Alert::error('Error', _lang('This class is limited to a maximum number of groups.').' ('.$class->max_groups.') '._lang('Please choose an existing group for this class, or raise the limit in Class settings.'));

                return redirect('subjects')->withInput();
            }
        }

        if (Subject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', $request->class_id)
            ->where('group_id', $request->group_id)
            ->where('subject_code', $request->subject_code)
            ->exists()
        ) {
            Alert::error('Error', _lang('Subject Already Added For This Code. Please Change Code'));

            return redirect('subjects');
        } else {
            $subject = new Subject;
            $subject->institute_id = get_institute_id();
            $subject->branch_id = get_branch_id();
            $subject->class_id = $request->class_id;
            $subject->group_id = $request->group_id;
            $subject->subject_name = $request->subject_name;
            $subject->subject_code = $request->subject_code;
            $subject->subject_short_form = $request->subject_short_form;
            $subject->subject_type = $request->subject_type;
            $subject->subject_type_form = $request->subject_type_form;
            $subject->sl_no = $request->sl_no;
            $subject->save();

            Alert::toast('Subject has been created successfully.', 'success');

            // Redirect (instead of returning the list view directly) so a
            // page refresh after saving doesn't re-submit the same form and
            // silently create a duplicate subject.
            return redirect()->route('subjects.class-wise', $subject->class_id);
        }
    }

    public function get_subject(Request $request): string
    {
        $results = Subject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', $request->class_id)
            ->orderBy('id', 'DESC')
            ->get();
        $subjects = '';
        $subjects .= '<option value="" disabled>Select One</option>';
        foreach ($results as $data) {
            $subjects .= '<option value="'.$data->id.'">'.e($data->subject_name).'</option>';
        }

        return $subjects;
    }

    private function findSubjectOrFail(int $id): Subject
    {
        $subject = Subject::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);

        abort_if(! $subject, 404, _lang('Subject not found.'));

        return $subject;
    }

    public function edit(int $id): Factory|View
    {
        $subject = $this->findSubjectOrFail($id);

        return view('academic::subjects.subject-edit', compact('subject'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        // See the matching comment in store() — these limits mirror the
        // actual `subjects` table columns (subject_name varchar(40) NOT
        // NULL, subject_code varchar(20) NOT NULL).
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'nullable|numeric',
            'subject_name' => 'required|string|max:40',
            'subject_code' => 'required|string|max:20',
            'subject_short_form' => 'nullable|string|max:191',
            'subject_type' => 'nullable|string|max:191',
            'subject_type_form' => 'nullable|string|max:191',
            'sl_no' => 'nullable|integer|min:0',
        ]);

        if ($request->group_id) {
            $class = ClassModel::find($request->class_id);
            if ($class && $class->wouldExceedGroupLimit((int) $request->group_id)) {
                Alert::error('Error', _lang('This class is limited to a maximum number of groups.').' ('.$class->max_groups.') '._lang('Please choose an existing group for this class, or raise the limit in Class settings.'));

                return redirect()->back()->withInput();
            }
        }

        $subject = $this->findSubjectOrFail($id);
        $subject->class_id = $request->class_id;
        $subject->group_id = $request->group_id;
        $subject->subject_name = $request->subject_name;
        $subject->subject_code = $request->subject_code;
        $subject->subject_short_form = $request->subject_short_form;
        $subject->subject_type = $request->subject_type;
        $subject->subject_type_form = $request->subject_type_form;
        $subject->sl_no = $request->sl_no;
        $subject->save();

        Alert::toast('Subject has been updated successfully.', 'success');

        return redirect()->route('subjects.class-wise', $subject->class_id);
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $subject = $this->findSubjectOrFail($id);
        $subject->delete();

        Alert::toast('Subject has been delete successfully.', 'success');

        return redirect('subjects');
    }
}