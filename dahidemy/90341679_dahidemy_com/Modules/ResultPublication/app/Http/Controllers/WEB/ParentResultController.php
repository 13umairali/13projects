<?php

namespace Modules\ResultPublication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\Academic\Models\Student;
use Modules\ParentModule\Models\ParentModel;
use Modules\ResultPublication\Models\ResultPublication;

class ParentResultController extends Controller
{
    public function index(): Factory|View
    {
        $childRecords = ParentModel::where('user_id', auth()->id())->pluck('student_id');

        $children = Student::whereIn('id', $childRecords)->with('studentSession')->get();

        $byChild = [];
        foreach ($children as $child) {
            $session = $child->studentSession;
            $publications = $session
                ? ResultPublication::visibleToClassSection($session->class_id, $session->section_id)
                    ->with(['subject', 'exam', 'onlineExam'])
                    ->where('institute_id', get_institute_id())
                    ->get()
                : collect();

            $byChild[] = [
                'student' => $child,
                'publications' => $publications,
            ];
        }

        return view('resultpublication::parent.index', ['byChild' => $byChild]);
    }
}
