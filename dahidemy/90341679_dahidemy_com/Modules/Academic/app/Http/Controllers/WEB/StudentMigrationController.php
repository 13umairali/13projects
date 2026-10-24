<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentMigration;
use Modules\Academic\Models\StudentSession;
use RealRashid\SweetAlert\Facades\Alert;

class StudentMigrationController extends Controller
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index()
    {
        if (Route::currentRouteName() === 'student-migration.index') {
            $type = 'merit_and_without_merit';

            return view('academic::migration.index', compact('type'));
        } elseif (Route::currentRouteName() === 'student-migration-pushback.index') {
            $type = 'pushback';

            return view('academic::migration.index', compact('type'));
        }
    }

    public function migration(): Factory|View
    {
        return view('academic::migration.index');
    }

    public function store(Request $request)
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $request->validate([
            'users' => 'required|array|min:1',
            'users.*' => Rule::exists('students', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            'class_id' => ['required', 'integer', Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'migrate_class_id' => ['required', 'integer', Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'migrate_section_id' => ['required', 'integer', Rule::exists('sections', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'group_id' => ['nullable', 'integer', Rule::exists('student_groups', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId))],
            'prev_roll' => 'required|array',
            'new_roll' => 'required|array',
            'new_roll.*' => 'required|integer|min:1',
            'session_table_id' => 'required|array',
            'session_table_id.*' => Rule::exists('student_sessions', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            'type' => 'required|string',
            'academic_year' => ['required', 'numeric', Rule::exists('academic_years', 'id')],
        ]);

        $this->academicContext->assertClassSectionGroup(
            (int) $request->class_id,
            (int) $request->section_id,
        );
        // Not passing group_id here — same reasoning as the Add Student fix:
        // assertClassSectionGroup's group check requires the group to
        // already be in use in this exact class/section, which incorrectly
        // rejects the first-ever migration of a student into a new
        // class/section/group combination. 'group_id' is already validated
        // above as a real group belonging to this institute, which is all
        // that should be required here.
        $this->academicContext->assertClassSectionGroup(
            (int) $request->migrate_class_id,
            (int) $request->migrate_section_id,
        );

        // Reject the whole batch up front if any submitted roll would collide with
        // another student already in the destination class/section/session — the
        // DB's own unique_combination constraint includes student_id, so it can
        // never catch two different students sharing a roll.
        $duplicateRoll = StudentSession::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('session_id', $request->academic_year)
            ->where('class_id', $request->migrate_class_id)
            ->where('section_id', $request->migrate_section_id)
            ->whereIn('roll', array_unique($request->new_roll))
            ->whereNotIn('student_id', $request->users)
            ->exists();

        if ($duplicateRoll) {
            Alert::error('Error', _lang('One or more of the new roll numbers is already assigned to another student in the destination class/section.'));

            return back()->withInput();
        }

        try {
            DB::transaction(function () use ($request, $instituteId, $branchId) {
                foreach ($request->users as $key => $studentId) {
                    $student = Student::where('institute_id', $instituteId)
                        ->where('branch_id', $branchId)
                        ->find($studentId);

                    if (! $student) {
                        continue;
                    }

                    $session = StudentSession::where('institute_id', $instituteId)
                        ->where('branch_id', $branchId)
                        ->find($request->session_table_id[$key] ?? null);

                    // Capture the session's current group before it's overwritten below —
                    // this is the authoritative "previous group", not students.group.
                    $prevGroupId = $session?->group_id;

                    // ✅ MIGRATION LOG
                    StudentMigration::create([
                        'institute_id' => $instituteId,
                        'branch_id' => $branchId,
                        'student_id' => $studentId,
                        'prev_section_id' => $request->section_id,
                        'section_id' => $request->migrate_section_id,
                        'group_id' => $request->group_id,
                        'prev_group_id' => $prevGroupId,
                        'prev_class_id' => $request->class_id,
                        'class_id' => $request->migrate_class_id,
                        'new_roll' => $request->new_roll[$key],
                        'prev_roll' => $request->prev_roll[$key] ?? null,
                        'type' => $request->type,
                        'academic_year' => $request->academic_year,
                        // "If transferring to another school, require a
                        // 'School Name' input field" — captured here when
                        // provided. Deliberately not changing what happens
                        // to the student's session on this type of record;
                        // see the migration's own comment for why.
                        'transferred_to_school_name' => $request->transferred_to_school_name,
                    ]);

                    // ✅ UPDATE SESSION
                    if ($session) {
                        $session->update([
                            'session_id' => $request->academic_year,
                            'class_id' => $request->migrate_class_id,
                            'section_id' => $request->migrate_section_id,
                            'roll' => $request->new_roll[$key],
                            'group_id' => $request->group_id,
                        ]);
                    }

                }
            });

            Alert::success('Success', 'Students migrated successfully');

            return back();
        } catch (Exception $e) {
            Log::error('Student migration failed: '.$e->getMessage(), ['exception' => $e]);
            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function get_student_for_migration($class_id, $section_id)
    {
        $students = Student::join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->select('students.*', 'student_sessions.roll', 'student_sessions.id as session_table_id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->when($class_id, fn ($q) => $q->where('student_sessions.class_id', $class_id))
            ->when($section_id, fn ($q) => $q->where('student_sessions.section_id', $section_id))
            ->with('studentSession.group')
            ->get();

        return view('academic::migration.student-filter-data', compact('students'));
    }

    public function migratedList(Request $request): Factory|View
    {
        $sectionId = $request->section_id;
        $academicYearId = (int) $request->academic_year_id;

        $migratedLists = $this->migratedListQuery($request)->get();

        return view('academic::migration.migration_list', [
            'sectionId' => $sectionId,
            'academicYearId' => $academicYearId,
            'migratedLists' => $migratedLists,
        ]);
    }

    /**
     * "Add Export to PDF & Excel functionality to ... Migrated List."
     * Reuses migratedListQuery() so the export always matches exactly
     * what the list page would show for the same filters.
     */
    public function exportMigratedListPdf(Request $request)
    {
        $migratedLists = $this->migratedListQuery($request)->get();

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('academic::migration.migrated-list-pdf', compact('migratedLists'))
            ->setPaper('a4', 'landscape')
            ->download('migrated-list-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportMigratedListExcel(Request $request)
    {
        $migratedLists = $this->migratedListQuery($request)->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MigratedListExport($migratedLists),
            'migrated-list-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    private function migratedListQuery(Request $request)
    {
        $sectionId = $request->section_id;
        $academicYearId = (int) $request->academic_year_id;

        // student_migrations/students/student_sessions all have columns named
        // id/institute_id/branch_id/class_id/section_id — selecting '*' from all
        // three let the last-joined table's value silently win for every
        // colliding column name (e.g. ->id resolved to student_sessions.id, not
        // the migration log's own id). Alias every ambiguous column explicitly.
        // Shared by migratedList() (the list page) and the PDF/Excel export
        // methods below, so both always agree on exactly the same data.
        $query = StudentMigration::query()
            ->with('class', 'section', 'migratedToClass', 'migratedToSection')
            ->select(
                'student_migrations.id as id',
                'student_migrations.type',
                'student_migrations.prev_class_id',
                'student_migrations.prev_section_id',
                'student_migrations.prev_group_id',
                'student_migrations.prev_roll',
                'student_migrations.class_id',
                'student_migrations.section_id',
                'student_migrations.group_id as migrated_group_id',
                'student_migrations.new_roll',
                'student_migrations.transferred_to_school_name',
                'student_migrations.academic_year',
                'student_migrations.created_at as migrated_at',
                'students.id as student_id',
                'students.first_name',
                'students.last_name',
                'students.registration_no',
                'student_sessions.roll',
                'student_sessions.class_id as current_class_id',
                'student_sessions.section_id as current_section_id',
            )
            ->leftJoin('students', 'students.id', '=', 'student_migrations.student_id')
            ->leftJoin('student_sessions', function ($join) {
                $join->on('students.id', '=', 'student_sessions.student_id')
                    ->whereNull('student_sessions.deleted_at');
            })
            ->where('student_migrations.institute_id', get_institute_id())
            ->where('student_migrations.branch_id', get_branch_id())
            ->where('student_migrations.academic_year', $academicYearId);

        if ($sectionId && $sectionId != 'all') {
            $query->where('student_migrations.section_id', $sectionId);
        }

        return $query->orderBy('student_sessions.roll', 'ASC');
    }
}
