<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\StudentAttendance;

class QrAttendanceController extends Controller
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index()
    {
        return view('academic::qrattendance.scanner');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required',
        ]);

        $qrCode = $request->qr_code;
        if (! $qrCode) {
            return $this->responseError([], 'QR Code not found.', 404);
        }

        $studentSession = $this->academicContext->findStudentSessionByQrCode($qrCode);
        if (! $studentSession) {
            return $this->responseError([], 'Student Session not found.', 404);
        }

        $existingAttendance = StudentAttendance::where('student_id', $studentSession->student_id)
            ->where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('period_id', 1)
            ->whereDate('date', Carbon::today())
            ->exists();

        if ($existingAttendance) {
            return $this->responseSuccess([], 'Attendance already taken for today.');
        }

        DB::beginTransaction();
        try {
            $attendance = StudentAttendance::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'student_id' => $studentSession->student_id,
                'class_id' => $studentSession->class_id,
                'section_id' => $studentSession->section_id,
                'period_id' => 1,
                'date' => Carbon::today()->toDateString(),
                'attendance' => 1,
            ]);

            DB::commit();

            return $this->responseSuccess($attendance, 'Attendance taken successfully.');
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }
}
