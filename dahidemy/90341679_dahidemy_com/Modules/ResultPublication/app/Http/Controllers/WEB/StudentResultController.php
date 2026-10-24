<?php

namespace Modules\ResultPublication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\Academic\Models\Student;
use Modules\ResultPublication\Models\ResultPublication;

class StudentResultController extends Controller
{
    public function index(): Factory|View
    {
        $student = Student::where('user_id', auth()->id())->with('studentSession')->first();
        $session = $student?->studentSession;

        $publications = $session
            ? ResultPublication::visibleToClassSection($session->class_id, $session->section_id)
                ->with(['subject', 'exam', 'onlineExam'])
                ->where('institute_id', get_institute_id())
                ->get()
            : collect();

        return view('resultpublication::student.index', compact('publications'));
    }
}
