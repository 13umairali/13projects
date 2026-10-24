<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemConfiguration\Http\Controllers\API\UserLogsController;
use Modules\SystemConfiguration\Http\Controllers\API\UtilityController;

Route::middleware('auth:api')->group(function () {
    // Utility Controller
    Route::post('/drop-database', [UtilityController::class, 'dropDatabase']);
    Route::match(['get', 'post'], 'administration/general_settings', [UtilityController::class, 'settings']);
    Route::post('administration-change-year', [UtilityController::class, 'academicYearChange']);
    Route::post('administration-change-branch', [UtilityController::class, 'changeBranch']);
    Route::post('administration-upload-logo', [UtilityController::class, 'uploadLogo']);
    Route::get('administration-backup-database', [UtilityController::class, 'backupDatabase']);
    Route::apiResource('user-logs', UserLogsController::class);
});

Route::get('get-image-folder-urls', [UtilityController::class, 'getAllImageFolderUrls']);
Route::post('student-device-attendance', [UtilityController::class, 'studentDeviceAttendance']);
