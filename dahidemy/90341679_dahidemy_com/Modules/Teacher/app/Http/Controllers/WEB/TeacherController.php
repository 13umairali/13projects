<?php

namespace Modules\Teacher\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\Assignment;
use Modules\Academic\Models\ClassRoutine;
use Modules\Academic\Models\Teacher;

class TeacherController extends Controller
{
    public function my_profile(): Factory|View
    {
        $teacher = Teacher::select('*', 'teachers.id AS id')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.id', get_teacher_id())
            ->first();

        return view('teacher::profile', compact('teacher'));
    }

    public function class_schedule(): Factory|View
    {
        $routine = ClassRoutine::getTeacherRoutine(get_teacher_id());

        return view('teacher::class_schedule', compact('routine'));
    }

    public function assignments(): Factory|View
    {
        $assignments = Assignment::select(
            'assignments.*',
            'classes.class_name',
            'sections.section_name',
            'subjects.subject_name'
        )
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->whereNull('classes.deleted_at')
            ->join('sections', 'sections.id', '=', 'assignments.section_id')
            ->whereNull('sections.deleted_at')
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id')
            ->whereNull('subjects.deleted_at')
            ->join('assign_subjects', 'subjects.id', '=', 'assign_subjects.subject_id')
            ->where('assign_subjects.teacher_id', get_teacher_id())
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->orderBy('assignments.id', 'DESC')
            ->get();

        return view('teacher::assignments.assignment-list', compact('assignments'));
    }

    public function create_assignment(): Factory|View
    {
        return view('teacher::assignments.assignment-add');
    }

    public function store_assignment(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required',
            'section_id' => 'required',
            'subject_id' => 'required',
            'file' => 'required',
            'file_2' => 'nullable',
            'file_3' => 'nullable',
            'file_4' => 'nullable',
        ]);

        $assignment = new Assignment;
        $assignment->institute_id = get_institute_id();
        $assignment->branch_id = get_branch_id();
        $assignment->session_id = get_option('academic_year');
        $assignment->title = $request->title;
        $assignment->description = $request->description;
        $assignment->deadline = $request->deadline;
        $assignment->class_id = $request->class_id;
        $assignment->section_id = $request->section_id;
        $assignment->subject_id = $request->subject_id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file = $file_name;
        }
        if ($request->hasFile('file_2')) {
            $file = $request->file('file_2');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_2 = $file_name;
        }
        if ($request->hasFile('file_3')) {
            $file = $request->file('file_3');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_3 = $file_name;
        }
        if ($request->hasFile('file_4')) {
            $file = $request->file('file_4');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_4 = $file_name;
        }

        $assignment->save();

        return redirect('teacher/assignments')->with('success', _lang('Information has been added'));
    }

    public function edit_assignment($id): Factory|View
    {
        $assignment = Assignment::where('id', $id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())->first();

        return view('teacher::assignments.assignment-edit', compact('assignment'));
    }

    public function show_assignment($id): Factory|View
    {
        $assignment = Assignment::select('*', 'assignments.id AS id')
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->whereNull('classes.deleted_at')
            ->join('sections', 'sections.id', '=', 'assignments.class_id')
            ->whereNull('sections.deleted_at')
            ->join('subjects', 'subjects.id', '=', 'assignments.class_id')
            ->whereNull('subjects.deleted_at')
            ->where('assignments.id', $id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->first();

        return view('teacher::assignments.assignment-show', compact('assignment'));
    }

    public function update_assignment(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required',
            'section_id' => 'required',
            'subject_id' => 'required',
            'file' => 'nullable',
            'file_2' => 'nullable',
            'file_3' => 'nullable',
            'file_4' => 'nullable',
        ]);

        $assignment = Assignment::find($id);
        $assignment->institute_id = get_institute_id();
        $assignment->branch_id = get_branch_id();
        $assignment->session_id = get_option('academic_year');
        $assignment->title = $request->title;
        $assignment->description = $request->description;
        $assignment->deadline = $request->deadline;
        $assignment->class_id = $request->class_id;
        $assignment->section_id = $request->section_id;
        $assignment->subject_id = $request->subject_id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file = $file_name;
        }
        if ($request->hasFile('file_2')) {
            $file = $request->file('file_2');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_2 = $file_name;
        }
        if ($request->hasFile('file_3')) {
            $file = $request->file('file_3');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_3 = $file_name;
        }
        if ($request->hasFile('file_4')) {
            $file = $request->file('file_4');
            $file_name = time().rand(1, 999).'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/assignments/'), $file_name);
            $assignment->file_4 = $file_name;
        }

        $assignment->save();

        return redirect('teacher/assignments')->with('success', _lang('Information has been updated'));
    }

    public function destroy_assignment($id): Redirector|RedirectResponse
    {
        $assignment = Assignment::find($id);
        $assignment->delete();

        return redirect('teacher/assignments')->with('success', _lang('Information has been deleted'));
    }
}