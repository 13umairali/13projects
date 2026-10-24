<?php

namespace Modules\Library\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\LibraryMember;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Staff;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\Teacher;
use Modules\Authentication\Models\User;
use Modules\Library\DataTables\LibraryMemberDataTable;

class LibraryMemberController extends Controller
{
    public function index(LibraryMemberDataTable $dataTable)
    {
        return $dataTable->render('library::members.member-list');
    }

    public function create(): Factory|View
    {
        $teachers = Teacher::orderBy('id', 'DESC')->get();
        $staffs = Staff::orderBy('id', 'DESC')->get();

        return view('library::members.member-add', compact('teachers', 'staffs'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|unique:library_members',
            'member_type' => 'required',
        ]);

        $memberType = $request->member_type;
        $userId = intval($request->user_id);

        $member = new LibraryMember;
        $member->user_id = $userId;
        $member->member_type = $request->member_type;

        if ($memberType == 'Student') {
            $student = Student::where('user_id', $userId)->with('studentSession')->first();
            $id = $student->id;
            $member->library_id = intval($student->studentSession?->roll);
            $member->student_id = $id;
        } elseif ($memberType == 'Teacher') {
            $id = Teacher::where('user_id', $userId)->first()->id;
            $member->library_id = intval(date('Y').rand(100, 999).sprintf('%02d', $userId));
            $member->teacher_id = $id;
        } else {
            $id = Staff::where('user_id', $userId)->first()->id;
            $member->library_id = intval(date('Y').rand(100, 999).sprintf('%02d', $userId));
            $member->staff_id = $id;
        }

        $member->save();

        return redirect('librarymembers-librarycard/'.$member->id)->with('success', _lang('Information has been added'));
    }

    public function show(int $id): Redirector|RedirectResponse
    {
        $find = LibraryMember::find($id);
        $user_id = User::find($find->user_id)->id;
        if ($find->member_type == 'Student') {
            $id = Student::where('user_id', $user_id)->first()->id;

            return redirect('students/'.$id);
        } elseif ($find->member_type == 'Teacher') {
            $id = Teacher::where('user_id', $user_id)->first()->id;

            return redirect('teachers/'.$id);
        } else {
            $id = Staff::where('user_id', $user_id)->first()->id;

            return redirect('staffs/'.$id);
        }
    }

    public function library_card($id): Factory|View
    {
        $library = LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')
            ->where('library_members.id', $id)->first();

        // The card previously showed only name/photo/member type/library ID
        // -- for a Student it's genuinely more useful as an ID card if it
        // also shows Class/Section, and for Teacher/Staff their
        // Designation, pulled straight from their existing registration
        // record rather than asked for again.
        $classSection = null;
        $designation = null;

        if ($library && $library->member_type === 'Student' && $library->student_id) {
            $session = \Modules\Academic\Models\StudentSession::with(['class', 'section'])
                ->where('student_id', $library->student_id)
                ->where('session_id', get_option('academic_year'))
                ->first();
            if ($session) {
                $classSection = trim(($session->class->class_name ?? '').' - '.($session->section->section_name ?? ''), ' -');
            }
        } elseif ($library && $library->member_type === 'Teacher' && $library->teacher_id) {
            $designation = Teacher::find($library->teacher_id)?->designation;
        } elseif ($library && $library->member_type === 'Staff' && $library->staff_id) {
            $designation = Staff::find($library->staff_id)?->designation;
        }

        return view('library::members.library-card', compact('library', 'classSection', 'designation'));
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $member = LibraryMember::find($id);
        $member->delete();

        return redirect('librarymembers')->with('success', _lang('Information has been deleted'));
    }

    public function get_section(Request $request): string
    {
        $results = Section::where('class_id', $request->class_id)->orderBy('id', 'DESC')->get();
        $sections = '';
        $sections .= '<option value="">'._lang('Select One').'</option>';
        foreach ($results as $data) {
            $sections .= '<option value="'.$data->id.'">'.$data->section_name.'</option>';
        }

        return $sections;
    }

    public function get_student(Request $request): string
    {
        $results = Student::join(
            'student_sessions',
            'student_sessions.student_id',
            '=',
            'students.id'
        )
            ->join('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->whereNull('sections.deleted_at')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('student_sessions.section_id', intval($request->section_id))
            ->orderBy('students.id', 'DESC')
            ->get();
        $students = '';
        $students .= '<option value="">'._lang('Select One').'</option>';
        foreach ($results as $data) {
            $students .= '<option value="'.$data->user_id.'">'.$data->first_name.'</option>';
        }

        return $students;
    }
}