<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\SectionService;

class ExamEssentialsController extends Controller
{
    public function __construct(
        private readonly ClassService $classService,
        private readonly SectionService $sectionService
    ) {}

    public function index(): Factory|View
    {
        return view('academic::exam_essentials.search', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->sectionService->getSections(),
        ]);
    }

    public function store(Request $request)
    {
        $classId = $request->class_id;
        $sectionId = $request->section_id;
        $fromRoll = $request->from_roll;
        $toRoll = $request->to_roll;

        $query = StudentSession::where('class_id', $classId)->where('section_id', $sectionId);

        if ($fromRoll !== null && $toRoll !== null) {
            $query->whereBetween('roll', [$fromRoll, $toRoll]);
        }

        $data = $query->with('student', 'session', 'class', 'section', 'group')->get();

        if ($request->seat_plan === '1') {
            return view('academic::exam_essentials.seat_plan', compact('data'));
        }

        if ($request->admit_card === '1') {
            return view('academic::exam_essentials.admit_card', [
                'data' => $data,
                'classId' => $classId,
                'sectionId' => $sectionId,
                'fromRoll' => $fromRoll,
                'toRoll' => $toRoll,
            ]);
        }
    }

    /**
     * "Add Export to PDF/Excel for bulk printing... Admit Cards." The
     * admit_card view is already a self-contained, print-ready page with
     * page-break-after per card, so this just renders the exact same
     * view server-side to a downloadable PDF instead of the browser tab.
     */
    public function exportAdmitCardPdf(Request $request)
    {
        $data = $this->admitCardQuery($request)->get();

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('academic::exam_essentials.admit_card', compact('data'))
            ->setPaper('a4', 'portrait')
            ->download('admit-cards-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * "...and a 'Send through Email' option for Admit Cards." Sends each
     * student's own admit card (one PDF per student, not one combined
     * PDF) to their registered account email. Students without an email
     * on file are skipped and counted, not silently dropped -- the
     * admin needs to know who didn't get one.
     */
    public function emailAdmitCards(Request $request)
    {
        $sessions = $this->admitCardQuery($request)->get();

        $sent = 0;
        $skipped = 0;

        foreach ($sessions as $session) {
            $email = $session->student?->user?->email;

            if (! $email) {
                $skipped++;

                continue;
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('academic::exam_essentials.admit_card', [
                'data' => collect([$session]),
            ]);

            try {
                \Illuminate\Support\Facades\Mail::to($email)->send(
                    new \App\Mail\AdmitCardMail(
                        studentName: trim(($session->student->first_name ?? '').' '.($session->student->last_name ?? '')),
                        pdfContent: $pdf->output(),
                        fileName: 'admit-card-'.$session->roll.'.pdf',
                    )
                );
                $sent++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        \RealRashid\SweetAlert\Facades\Alert::toast(
            _lang('Admit cards sent: ').$sent.'. '._lang('Skipped (no email on file or send failed): ').$skipped,
            'success'
        );

        return redirect()->back();
    }

    private function admitCardQuery(Request $request)
    {
        $classId = $request->class_id;
        $sectionId = $request->section_id;
        $fromRoll = $request->from_roll;
        $toRoll = $request->to_roll;

        $query = StudentSession::where('class_id', $classId)->where('section_id', $sectionId);

        if ($fromRoll !== null && $toRoll !== null) {
            $query->whereBetween('roll', [$fromRoll, $toRoll]);
        }

        return $query->with('student.user', 'session', 'class', 'section', 'group');
    }
}
