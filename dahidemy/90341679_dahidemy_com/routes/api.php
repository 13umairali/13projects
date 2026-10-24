<?php

use App\Http\Controllers\API\ReferralController;
use App\Http\Controllers\API\ReferralSettingController;
use App\Http\Controllers\API\RoleOptionController;
use App\Http\Controllers\API\WithdrawalSettingController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    // SaaS Super Admin: configurable reward rules per referrer role.
    Route::apiResource('referral-settings', ReferralSettingController::class);
    Route::get('withdrawal-settings', [WithdrawalSettingController::class, 'show']);
    Route::put('withdrawal-settings', [WithdrawalSettingController::class, 'update']);

    // Every distinct role name in use platform-wide (built-in AND any
    // custom roles individual institutes have created) - powers the
    // Referrer Role dropdown so the admin picks from real roles instead
    // of typing blind.
    Route::get('role-options', [RoleOptionController::class, 'index']);

    // SaaS Super Admin: review and approve/reject referrals. GET, despite
    // being a write - matches the existing subscription/approve-request
    // convention already used elsewhere in this same file.
    Route::get('referrals', [ReferralController::class, 'index']);
    Route::get('referrals/{id}/approve', [ReferralController::class, 'approve']);
    Route::get('referrals/{id}/reject', [ReferralController::class, 'reject']);
});

Route::prefix('languages')->group(function () {
    Route::get('/', [LanguageController::class, 'index']);
    Route::post('/', [LanguageController::class, 'store']);
    Route::get('/export/{name}', [LanguageController::class, 'export']);
    Route::post('/update-key/{name}', [LanguageController::class, 'updateKey']);
    Route::post('/import/{name}', [LanguageController::class, 'import']);
    Route::post('/set-default/{name}', [LanguageController::class, 'setDefault']);
});

Route::get('/check-status', function () {
    try {
        DB::connection()->getPdo(); // test DB

        return response()->json([
            'status' => true,
            'message' => 'API and Database are working perfectly',
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ]);
    }
});