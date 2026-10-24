<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Assignment;
use Modules\Academic\Models\ClassRoutine;
use Modules\Academic\Models\Event;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Teacher;

class TeacherController extends Controller
{
    public function my_profile(): JsonResponse
    {
        $teacher = Teacher::select(
            'teachers.*',
            'teachers.id AS id',
            'teachers.user_id',
            'users.name',
            'users.phone',
            'users.email',
            'users.user_type',
            'users.role_id',
            'users.email',
        )
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.id', get_teacher_id())
            ->first();

        if (! $teacher) {
            return $this->responseError([], _lang('Something went wrong. Teacher Profile not found.'));
        }

        return $this->responseSuccess($teacher, 'Teacher Profile fetch successfully.');
    }

    public function class_schedule(): JsonResponse
    {
        $routine = ClassRoutine::getTeacherRoutine(get_teacher_id());

        if (! $routine) {
            return $this->responseError([], _lang('Something went wrong. Class Routine not found.'));
        }

        return $this->responseSuccess($routine, 'Class Routine fetch successfully.');
    }

    public function assignments(): JsonResponse
    {
        $assignments = Assignment::select('assignments.*', 'assignments.id AS id', 'classes.*', 'sections.*', 'subjects.*', 'assign_subjects.*')
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->whereNull('classes.deleted_at')
            ->join('sections', 'sections.id', '=', 'assignments.section_id') // Corrected this join
            ->whereNull('sections.deleted_at')
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id') // Corrected this join
            ->whereNull('subjects.deleted_at')
            ->join('assign_subjects', 'subjects.id', '=', 'assign_subjects.subject_id')
            ->where('assign_subjects.teacher_id', get_teacher_id())
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->groupBy('assignments.id', 'assignments.title', 'assignments.description', 'assignments.class_id', 'assignments.section_id', 'assignments.subject_id', 'classes.class_name', 'sections.section_name', 'subjects.subject_name', 'assign_subjects.teacher_id', 'assign_subjects.id') // Include all necessary columns
            ->orderBy('assignments.id', 'DESC')
            ->get();

        if (! $assignments) {
            return $this->responseError([], _lang('Something went wrong. Assignments not found.'));
        }

        return $this->responseSuccess($assignments, 'Assignments fetch successfully.');
    }

    public function store_assignment(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
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

        return $this->responseSuccess([], 'Assignments create successfully.');
    }

    public function edit_assignment(int $id): JsonResponse
    {
        $assignment = Assignment::where('id', $id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())->first();

        if (! $assignment) {
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'));
        }

        return $this->responseSuccess($assignment, 'Assignment fetch successfully.');
    }

    public function show_assignment(int $id): JsonResponse
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

        if (! $assignment) {
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'));
        }

        return $this->responseSuccess($assignment, 'Assignment fetch successfully.');
    }

    public function update_assignment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'file' => 'nullable',
            'file_2' => 'nullable',
            'file_3' => 'nullable',
            'file_4' => 'nullable',
        ]);

        $assignment = Assignment::find($id);
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

        return $this->responseSuccess([], 'Assignment update successfully.');
    }

    public function destroy_assignment(int $id): JsonResponse
    {
        $assignment = Assignment::find($id);
        if (! $assignment) {
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'));
        }

        $assignment->delete();

        return $this->responseSuccess($assignment, 'Assignment delete successfully.');
    }

    public function teacherNoticeGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $notices = Notice::select('id', 'title', 'notice', 'date', 'created_by')->with('userType')->paginate($perPage);

        return $this->responseSuccess($notices, 'Notices fetch successfully.');
    }

    public function teacherEventGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $events = Event::select('id', 'start_date', 'end_date', 'name', 'details', 'location')->paginate($perPage);

        return $this->responseSuccess($events, 'Events fetch successfully.');
    }
}