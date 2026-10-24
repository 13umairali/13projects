<?php

namespace Modules\LayoutCert\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Services\ClassService;
use Modules\LayoutCert\Models\CertificateIssue;
use Modules\LayoutCert\Models\CertificateTemplate;
use RealRashid\SweetAlert\Facades\Alert;

class LayoutCertController extends Controller
{
    public const TYPES = [
        'general-certificate' => 'General Recommendation Letter',
        'attendance-certificate' => 'Attendance Certificate',
        'transfer-certificate' => 'Transfer Certificate',
        'hsc-recommendation' => 'HSC Recommendation Letter',
        'abroad-recommendation' => 'Abroad Letter',
        'character-certificate' => 'Character Certificate',
        'study-certificate' => 'Study Certificate',
        'bonafide-certificate' => 'Bonafide Certificate',
        'migration-certificate' => 'Migration Certificate',
    ];

    public function __construct(
        private readonly ClassService $classService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(Request $request)
    {
        $type = $request->type;

        if (! array_key_exists($type, self::TYPES)) {
            Alert::error('Error', _lang('Invalid type'));

            return redirect('layout-cert');
        }

        $classes = $this->classService->getClasses();
        $typeLabel = self::TYPES[$type];

        return view('layoutcert::layout_cert.search', compact(
            'type',
            'typeLabel',
            'classes'
        ));
    }

    public function students(Request $request): string
    {
        $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
        ]);

        $classId = (int) $request->class_id;
        $sectionId = (int) $request->section_id;

        $this->academicContext->assertClassSectionGroup($classId, $sectionId);

        $sessions = $this->academicContext
            ->studentSessionQuery($classId, $sectionId)
            ->with('student')
            ->orderBy('roll')
            ->get();

        $output = '<option value="">'.e(_lang('Search by name or roll')).'</option>';

        foreach ($sessions as $row) {
            if (! $row->student || $row->roll === null || $row->roll === '') {
                continue;
            }

            $name = trim(($row->student->first_name ?? '').' '.($row->student->last_name ?? ''));
            $label = $name !== '' ? $name.' (Roll-'.$row->roll.')' : 'Roll-'.$row->roll;

            $output .= '<option value="'.e((string) $row->roll).'">'.e($label).'</option>';
        }

        return $output;
    }

    public function store(Request $request): Factory|Redirector|RedirectResponse|View
    {
        $request->validate([
            'type' => 'required|string|in:'.implode(',', array_keys(self::TYPES)),
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'roll' => 'required|string|max:15',
        ]);

        $resolved = $this->resolveCertificate(
            $request->type,
            (int) $request->class_id,
            (int) $request->section_id,
            $request->roll,
        );

        if (! $resolved) {
            Alert::error('Error', _lang('No Student Found'));

            return redirect()->back();
        }

        return view($resolved['view'], $resolved['data']);
    }

    /**
     * Sends the same certificate produced by store() as a PDF email
     * attachment instead of an inline HTML page. Shares resolveCertificate()
     * with store() so the two can never drift apart on template/data logic --
     * the previous version of this feature didn't exist at all, so there
     * was nothing to email a generated certificate to (only Preview/Download
     * existed, via the browser's own print-to-PDF, which produces nothing
     * on the server to attach to an email in the first place).
     */
    public function email(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => 'required|string|in:'.implode(',', array_keys(self::TYPES)),
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'roll' => 'required|string|max:15',
            'email' => 'required|email',
        ]);

        $resolved = $this->resolveCertificate(
            $request->type,
            (int) $request->class_id,
            (int) $request->section_id,
            $request->roll,
        );

        if (! $resolved) {
            Alert::error('Error', _lang('No Student Found'));

            return redirect()->back();
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($resolved['view'], $resolved['data']);
        $fileName = self::TYPES[$request->type].' - '.$resolved['data']['student']->first_name.'.pdf';

        try {
            \Illuminate\Support\Facades\Mail::to($request->email)->send(
                new \Modules\LayoutCert\Mail\CertificateMail(
                    certificateTypeLabel: self::TYPES[$request->type],
                    studentName: $resolved['data']['student']->first_name.' '.$resolved['data']['student']->last_name,
                    pdfContent: $pdf->output(),
                    fileName: $fileName,
                )
            );

            Alert::toast(_lang('Certificate emailed successfully.'), 'success');
        } catch (\Exception $e) {
            Alert::error('Error', _lang('Could not send the email. Please try again.'));
        }

        return redirect()->back();
    }

    /**
     * Shared by store() (inline preview) and email() (PDF attachment) so
     * both always agree on exactly which student and which template a
     * given type/class/section/roll combination resolves to.
     */
    private function resolveCertificate(string $type, int $classId, int $sectionId, string $roll): ?array
    {
        $this->academicContext->assertClassSectionGroup($classId, $sectionId);

        $studentSession = $this->academicContext
            ->studentSessionQuery($classId, $sectionId)
            ->with(['student', 'group', 'class', 'section', 'session'])
            ->where('roll', $roll)
            ->first();

        if (! $studentSession || ! $studentSession->student) {
            return null;
        }

        $student = $studentSession->student;
        $template = CertificateTemplate::forType($type);

        $fullName = trim($student->first_name.' '.$student->last_name);

        $issue = CertificateIssue::record($type, $student->id, $fullName, [
            'name' => $fullName,
            'roll' => $studentSession->roll,
            'class' => $studentSession->class?->class_name,
            'section' => $studentSession->section?->section_name,
            'group' => $studentSession->group?->group_name,
        ]);

        $verificationUrl = url('/certificate-verify/'.$issue->verification_code);

        $viewData = compact('studentSession', 'student', 'template', 'issue', 'verificationUrl');

        $view = match ($type) {
            'general-certificate' => 'layoutcert::layout_cert.general-format',
            'attendance-certificate' => 'layoutcert::layout_cert.attendance-format',
            'transfer-certificate' => 'layoutcert::layout_cert.transfer-format',
            'hsc-recommendation', 'abroad-recommendation' => 'layoutcert::layout_cert.recommendation-format',
            'character-certificate' => 'layoutcert::layout_cert.character-format',
            'study-certificate' => 'layoutcert::layout_cert.study-format',
            'bonafide-certificate' => 'layoutcert::layout_cert.bonafide-format',
            'migration-certificate' => 'layoutcert::layout_cert.migration-format',
            default => null,
        };

        if (! $view) {
            return null;
        }

        if ($type === 'hsc-recommendation' || $type === 'abroad-recommendation') {
            $viewData['type'] = $type;
        }

        return ['view' => $view, 'data' => $viewData];
    }

    /**
     * Public certificate verification — no auth required, matches the QR code / code printed
     * on the certificate against a real issuance record rather than re-deriving data from
     * current student records (which would "verify" a certificate against data that has
     * since changed).
     */
    public function verify(string $code): View
    {
        $issue = CertificateIssue::withoutGlobalScope('tenant')
            ->where('verification_code', strtoupper($code))
            ->first();

        return view('layoutcert::layout_cert.verify', [
            'issue' => $issue,
            'typeLabel' => $issue ? (self::TYPES[$issue->type] ?? $issue->type) : null,
        ]);
    }
}
