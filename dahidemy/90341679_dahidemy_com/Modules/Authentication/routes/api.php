<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\API\AuthController;
use Modules\Authentication\Http\Controllers\API\BranchController;
use Modules\Authentication\Http\Controllers\API\DashboardController;
use Modules\Authentication\Http\Controllers\API\InstituteController;
use Modules\Authentication\Http\Controllers\API\InstituteImageSAASSettingController;
use Modules\Authentication\Http\Controllers\API\InstituteImageSettingController;
use Modules\Authentication\Http\Controllers\API\InstituteTypeController;
use Modules\Authentication\Http\Controllers\API\OnboardingsController;
use Modules\Authentication\Http\Controllers\API\PermissionController;
use Modules\Authentication\Http\Controllers\API\PlanController;
use Modules\Authentication\Http\Controllers\API\RoleController;
use Modules\Authentication\Http\Controllers\API\SAASBannerController;
use Modules\Authentication\Http\Controllers\API\SAASBenefitController;
use Modules\Authentication\Http\Controllers\API\SAASCoreController;
use Modules\Authentication\Http\Controllers\API\SAASFaqController;
use Modules\Authentication\Http\Controllers\API\SAASFeedbackController;
use Modules\Authentication\Http\Controllers\API\SAASPanelController;
use Modules\Authentication\Http\Controllers\API\SAASRoleController;
use Modules\Authentication\Http\Controllers\API\SAASSolutionController;
use Modules\Authentication\Http\Controllers\API\SAASSubscriptionController;
use Modules\Authentication\Http\Controllers\API\UserController;
use Modules\Authentication\Http\Controllers\API\UtilityController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->middleware('throttle:6,1')->group(function () {
    Route::post('/login', 'login');
    Route::post('/saas-login', 'saasLogin');
    // Public self-registration removed: it created accounts with institute_id = NULL, which the
    // tenant scope treats as "platform staff, no filtering". Nothing in the SaaS app calls it.
});

Route::middleware('auth:api')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/profile', 'user');
        Route::post('/logout', 'logout');
        Route::post('/change-password', 'changePassword');
        Route::post('/profile-update', 'profileUpdate');
    });

    Route::get('institutes-status-update/{instituteId}', [InstituteController::class, 'statusUpdate'])->middleware('saas.admin');
    Route::apiResource('institutes', InstituteController::class)->middleware('saas.admin');
    // "Login as institute" from the SaaS panel: returns a single-use link (SAAS Admin only).
    Route::post('institutes/{id}/impersonate', [\Modules\Authentication\Http\Controllers\API\ImpersonationController::class, 'start'])
        ->middleware(['saas.admin', 'throttle:10,1'])
        ->where('id', '[0-9]+');
    Route::apiResource('branches', BranchController::class)->middleware('plan.limit:branch');
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('plans', PlanController::class)->middleware('saas.admin');
    Route::apiResource('saas-roles', SAASRoleController::class)->middleware('saas.admin');

    Route::get('dashboard-data', [DashboardController::class, 'index']);
    Route::get('student-attendance-summery', [DashboardController::class, 'studentAttendanceSummary']);

    // Onboarding
    Route::get('onboardings-list', [OnboardingsController::class, 'index'])->middleware('saas.admin');
    Route::get('onboardings-approve/{onboardingId}', [OnboardingsController::class, 'approve'])->middleware('saas.admin');
    Route::post('/subscription/upgrade', [OnboardingsController::class, 'upgrade']);

    Route::prefix('subscription')->group(function () {
        Route::get('/request-upgrade-list', [OnboardingsController::class, 'requestUpgradeList'])->middleware('saas.admin');
        Route::post('/request-upgrade', [OnboardingsController::class, 'requestUpgrade']);
        Route::get('/approve-request/{id}', [OnboardingsController::class, 'approveRequest'])->middleware('saas.admin');
        Route::post('/payment-upgrade', [OnboardingsController::class, 'paymentSubscriptions']);
    });

    // Image Upload settings
    Route::apiResource('institute-image-settings', InstituteImageSettingController::class);
    Route::apiResource('institute-image-saas-settings', InstituteImageSAASSettingController::class)->middleware('saas.admin');

    Route::post('saas-settings-update', [DashboardController::class, 'saasSettingsUpdate'])->middleware('saas.admin');
    Route::post('saas-settings-upload-logo', [DashboardController::class, 'uploadLogo'])->middleware('saas.admin');
    Route::post('saas-settings-mail-update', [DashboardController::class, 'updateMailConfig'])->middleware('saas.admin');
    Route::get('saas-settings-mail-config', [DashboardController::class, 'getMailConfig'])->middleware('saas.admin');

    // SAAS Panel CRUD
    Route::get('saas-dashboard-data', [DashboardController::class, 'saasDashboardData'])->middleware('saas.admin');
    Route::apiResource('saas-banners', SAASBannerController::class)->middleware('saas.admin');
    Route::apiResource('saas-panels', SAASPanelController::class)->middleware('saas.admin');
    Route::apiResource('saas-cores', SAASCoreController::class)->middleware('saas.admin');
    Route::apiResource('saas-solutions', SAASSolutionController::class)->middleware('saas.admin');
    Route::apiResource('saas-benefits', SAASBenefitController::class)->middleware('saas.admin');
    Route::apiResource('saas-feedback', SAASFeedbackController::class)->middleware('saas.admin');
    Route::apiResource('saas-faqs', SAASFaqController::class)->middleware('saas.admin');
    Route::apiResource('saas-subscriptions', SAASSubscriptionController::class)->middleware('saas.admin');
    Route::apiResource('institute-types', InstituteTypeController::class)->middleware('saas.admin');
    Route::get('saas-contacts', [SAASSubscriptionController::class, 'saasContacts'])->middleware('saas.admin');
});

Route::prefix('saas')->group(function () {
    // SAAS Panel CRUD
    Route::get('banners', [DashboardController::class, 'getBanners']);
    Route::get('packages', [DashboardController::class, 'getPackages']);
    Route::get('panels', [DashboardController::class, 'getPanels']);
    Route::get('cores', [DashboardController::class, 'getCores']);
    Route::get('solutions', [DashboardController::class, 'getSolutions']);
    Route::get('benefits', [DashboardController::class, 'getBenefits']);
    Route::get('feedback', [DashboardController::class, 'getFeedback']);
    Route::get('faqs', [DashboardController::class, 'getFaqs']);
    Route::get('policies', [DashboardController::class, 'saasPolicy']);
    Route::get('institute-types', [DashboardController::class, 'saasInstituteType']);
    Route::post('subscribe', [SAASSubscriptionController::class, 'publicStore']);
    Route::post('contacts', [SAASSubscriptionController::class, 'saasContact']);
});

Route::get('get-saas-settings', [DashboardController::class, 'getSAASSettings']);
Route::get('system/link-storage', [UtilityController::class, 'linkStorage'])->middleware(['auth:api', 'saas.admin']);
Route::get('system/unlink-storage', [UtilityController::class, 'unlinkStorage'])->middleware(['auth:api', 'saas.admin']);
Route::get('system/clear-cache', [UtilityController::class, 'clearAllCache'])->middleware(['auth:api', 'saas.admin']);
Route::get('system/all-students-get', [UtilityController::class, 'allStudentsGet'])->middleware('auth:api');
Route::get('v1/system/domain-check', [UtilityController::class, 'domainCheck']);
//Route::get('saas-account-activation', [UtilityController::class, 'saasAccountActivation']);
//Route::get('saas-account-status', [UtilityController::class, 'saasAccountStatus']);
Route::get('saas-stats', [UtilityController::class, 'saasStats']);
