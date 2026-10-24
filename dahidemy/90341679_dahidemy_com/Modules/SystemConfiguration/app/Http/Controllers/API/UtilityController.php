<?php

namespace Modules\SystemConfiguration\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Authentication\Models\User;
use Modules\SystemConfiguration\Http\Requests\Settings\SettingUpdateRequest;
use Modules\SystemConfiguration\Models\Setting;

class UtilityController extends Controller
{
    public function academicYearChange(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|integer',
        ]);

        $data = [];
        $data['value'] = intval($request->session_id);
        $data['updated_at'] = Carbon::now();
        Setting::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('name', 'academic_year')->update($data);

        return $this->responseSuccess(
            [$data['value']],
            _lang('Session Changed Successfully.')
        );
    }

    public function changeBranch(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|integer',
        ]);

        $branch_id = intval($request->branch_id);
        if ($branch_id != '') {
            User::where('id', auth()->user()->id)->update([
                'branch_id' => $branch_id,
            ]);
        }

        return $this->responseSuccess(
            [$branch_id],
            _lang('Branch Changed Successfully.')
        );
    }

    public function backupDatabase()
    {
        // A raw SQL dump necessarily contains every tenant's data — restrict it to a
        // platform-level SAAS Admin rather than any authenticated institute user.
        abort_unless(optional(auth()->user())->user_type === 'SAAS Admin', 403, 'Only a SAAS Admin can export a database backup.');

        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $return = '';
        $database = 'Tables_in_'.DB::getDatabaseName();
        $tables = [];
        $result = DB::select('SHOW TABLES');

        foreach ($result as $table) {
            $tables[] = $table->$database;
        }

        // loop through the tables
        foreach ($tables as $table) {
            $return .= "DROP TABLE IF EXISTS $table;";
            $result2 = DB::select("SHOW CREATE TABLE $table");
            $row2 = $result2[0]->{'Create Table'};
            $return .= "\n\n".$row2.";\n\n";
            $result = DB::select("SELECT * FROM $table");

            foreach ($result as $row) {
                $return .= "INSERT INTO $table VALUES(";
                foreach ($row as $key => $val) {
                    $return .= "'".addslashes($val)."',";
                }
                $return = substr_replace($return, '', -1);
                $return .= ");\n";
            }

            $return .= "\n\n\n";
        }

        // Save outside the public webroot so the dump can never be fetched directly by URL.
        $directory = storage_path('app/backups');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $file = $directory.DIRECTORY_SEPARATOR.'DB-BACKUP-'.now()->format('Y-m-d_His').'-'.Str::random(12).'.sql';
        file_put_contents($file, $return);

        return response()->download($file)->deleteFileAfterSend(true);
    }

    public function settings(SettingUpdateRequest $request): JsonResponse
    {
        try {
            if ($request->isMethod('get')) {
                $settings = Setting::where('institute_id', get_institute_id())
                    // ->where('branch_id', get_branch_id())
                    ->pluck('value', 'name');

                return $this->responseSuccess($settings, _lang('System Settings fetched successfully.'));
            }

            // If POST request, update or create settings
            DB::beginTransaction();
            foreach ($request->validated() as $key => $value) {
                if ($key === '_token') {
                    continue;
                }

                Setting::updateOrInsert(
                    [
                        'institute_id' => get_institute_id(),
                        // 'branch_id' => get_branch_id(),
                        'name' => $key,
                    ],
                    [
                        'value' => $value,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
            DB::commit();

            return $this->responseSuccess([], _lang('System Settings have been successfully updated.'));
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError(['error' => $e->getMessage()], _lang('Failed to store records unsuccessfully.'));
        }
    }

    public function dropDatabase(Request $request)
    {
        if (! app()->isLocal()) {
            return response()->json(['error' => 'Operation not allowed in this environment.'], 403);
        }

        $configuredToken = env('DB_DROP_TOKEN');
        $suppliedToken = (string) $request->input('token', '');

        // Require a non-empty, explicitly configured token — an unset DB_DROP_TOKEN must
        // never make this endpoint pass with an empty/missing "token" parameter.
        if (empty($configuredToken) || ! hash_equals((string) $configuredToken, $suppliedToken)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (optional(auth()->user())->user_type !== 'SAAS Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $dbName = DB::getDatabaseName();
            DB::statement("DROP DATABASE `$dbName`");
            DB::statement("CREATE DATABASE `$dbName`");

            return response()->json(['message' => "Database `$dbName` dropped successfully."]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:8192',
        ]);

        try {
            $instituteId = get_institute_id();
            $branchId = get_branch_id();

            $imageUrl = null;
            if (isset($request['logo'])) {
                if (isset($request['logo']) && $request['logo']->isValid()) {
                    $imageUrl = fileUploader('logos/', 'png', $request['logo']);
                }
            }

            // Prepare data
            $data = [
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'name' => 'logo',
                'value' => $imageUrl,
                'updated_at' => Carbon::now(),
            ];

            // Update or create logo setting
            $setting = Setting::updateOrCreate(
                [
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'name' => 'logo',
                ],
                $data
            );

            return $this->responseSuccess(
                $setting,
                _lang('System logo has been successfully uploaded.')
            );
        } catch (Exception $e) {
            return $this->responseError([], _lang('Logo upload failed: ').$e->getMessage());
        }
    }

    public function getAllImageFolderUrls(): JsonResponse
    {
        $base_url = url('/public/uploads');

        // Define dynamic folders
        $folders = [
            'backup',
            'files',
            'files/assignments',
            'files/syllabus',
            'images',
            'images/librarians',
            'images/staffs',
            'images/students',
            'images/teachers',
            'images/users',
            'signatures',
        ];

        $data = [];
        // Generate dynamic folder URLs
        foreach ($folders as $folder) {
            $key = str_replace(['/', '\\'], '_', $folder).'_url';
            $data[$key] = str_replace('\\', '/', $base_url.'/'.$folder.'/');
        }

        // Add specific static URLs
        $staticUrls = [
            'system_logo' => url('/public/uploads/logos/'),
        ];

        // Merge static URLs with dynamic URLs
        $data = array_merge($data, $staticUrls);

        return response()->json([
            'status' => 'success',
            'message' => 'All image folder URLs fetched successfully.',
            'data' => $data,
        ]);
    }

    public function studentDeviceAttendance(Request $request): JsonResponse
    {
        $logs = $request->input('logs', []);

        if (empty($logs)) {
            return response()->json([
                'status' => false,
                'message' => 'No attendance logs provided.',
            ], 400);
        }

        // ✅ Step 1: Collect all QR codes
        $qrCodes = collect($logs)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // ✅ Step 2: Fetch all student-session data in ONE query
        $students = DB::table('student_sessions as ss')
            ->join('students as s', 's.id', '=', 'ss.student_id')
            ->whereIn('ss.qr_code', $qrCodes)
            ->select(
                'ss.qr_code',
                'ss.class_id',
                'ss.section_id',
                's.id as student_id',
                's.institute_id',
                's.branch_id'
            )
            ->get()
            ->keyBy('qr_code'); // 🔥 key by QR for fast lookup

        $attendanceData = [];

        // ✅ Step 3: Build attendance data (NO DB query inside loop)
        foreach ($logs as $log) {
            $qrCode = $log['id'] ?? null;
            $checkTime = $log['timestamp'] ?? null;

            if (! $qrCode || ! $checkTime || ! isset($students[$qrCode])) {
                continue;
            }

            $student = $students[$qrCode];

            $attendanceData[] = [
                'institute_id' => $student->institute_id,
                'branch_id' => $student->branch_id,
                'student_id' => $student->student_id,
                'class_id' => $student->class_id,
                'section_id' => $student->section_id,
                'period_id' => 1,
                'date' => Carbon::parse($checkTime)->toDateString(),
                'attendance' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($attendanceData)) {
            return response()->json([
                'status' => true,
                'message' => 'No valid attendance logs to insert.',
            ]);
        }

        // ✅ Step 4: Remove duplicates inside request (IMPORTANT)
        $attendanceData = collect($attendanceData)
            ->unique(fn ($item) => $item['student_id'].'_'.$item['date'])
            ->values()
            ->all();

        // ✅ Step 5: Bulk upsert (FAST)
        DB::table('student_attendances')->upsert(
            $attendanceData,
            ['institute_id', 'branch_id', 'class_id', 'section_id', 'period_id', 'student_id', 'date'],
            ['attendance', 'updated_at']
        );

        return response()->json([
            'status' => true,
            'message' => count($attendanceData).' attendance records processed successfully.',
            'inserted' => count($attendanceData),
        ]);
    }
}
