<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\Auth\AuthenticatedSessionController;
use Modules\Authentication\Http\Controllers\Auth\ConfirmablePasswordController;
use Modules\Authentication\Http\Controllers\Auth\EmailVerificationNotificationController;
use Modules\Authentication\Http\Controllers\Auth\EmailVerificationPromptController;
use Modules\Authentication\Http\Controllers\Auth\NewPasswordController;
use Modules\Authentication\Http\Controllers\Auth\PasswordController;
use Modules\Authentication\Http\Controllers\Auth\PasswordResetLinkController;
use Modules\Authentication\Http\Controllers\Auth\RegisteredUserController;
use Modules\Authentication\Http\Controllers\Auth\VerifyEmailController;
use Modules\Authentication\Http\Controllers\ProfileController;
use Modules\Authentication\Http\Controllers\WEB\BranchController;
use Modules\Authentication\Http\Controllers\WEB\DashboardController;
use Modules\Authentication\Http\Controllers\WEB\InstituteController;
use Modules\Authentication\Http\Controllers\WEB\InstituteReferralController;
use Modules\Authentication\Http\Controllers\WEB\InstituteReferralSettingController;
use Modules\Authentication\Http\Controllers\WEB\InternalReferralController;
use Modules\Authentication\Http\Controllers\WEB\InstituteWithdrawalController;
use Modules\Authentication\Http\Controllers\WEB\InstituteWithdrawalSettingController;
use Modules\Authentication\Http\Controllers\WEB\LanguageController;
use Modules\Authentication\Http\Controllers\WEB\MyReferralController;
use Modules\Authentication\Http\Controllers\WEB\MySubscriptionController;
use Modules\Authentication\Http\Controllers\WEB\PlanController;
use Modules\Authentication\Http\Controllers\WEB\RoleController;
use Modules\Authentication\Http\Controllers\WEB\UserController;

Route::get('/language/{lang}', [LanguageController::class, 'index'])->name('language.switch');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/my-subscription', [MySubscriptionController::class, 'index'])->name('my-subscription.index');
    Route::post('/my-subscription/checkout', [MySubscriptionController::class, 'checkout'])->name('my-subscription.checkout');
    Route::get('/my-subscription/checkout-success', [MySubscriptionController::class, 'checkoutSuccess'])->name('my-subscription.checkout-success');

    Route::get('/my-referrals', [MyReferralController::class, 'index'])->name('my-referrals.index');
    Route::post('/my-referrals/request-withdrawal', [MyReferralController::class, 'requestWithdrawal'])->name('my-referrals.request-withdrawal');

    // Institute admin's own reward rules for their own users' referrals.
    Route::get('/institute-referral-settings', [InstituteReferralSettingController::class, 'index'])->name('institute-referral-settings.index');
    Route::post('/institute-referral-settings', [InstituteReferralSettingController::class, 'store'])->name('institute-referral-settings.store');
    Route::put('/institute-referral-settings/{id}', [InstituteReferralSettingController::class, 'update'])->name('institute-referral-settings.update');
    Route::delete('/institute-referral-settings/{id}', [InstituteReferralSettingController::class, 'destroy'])->name('institute-referral-settings.destroy');
    Route::post('/institute-referral-settings/toggle', [InstituteReferralSettingController::class, 'toggleInternalReferrals'])->name('institute-referral-settings.toggle');

    // Institute admin's review/approve queue for the institute-level reward.
    Route::get('/institute-referrals', [InstituteReferralController::class, 'index'])->name('institute-referrals.index');
    Route::post('/institute-referrals/{id}/approve', [InstituteReferralController::class, 'approve'])->name('institute-referrals.approve');
    Route::post('/institute-referrals/{id}/reject', [InstituteReferralController::class, 'reject'])->name('institute-referrals.reject');

    // Member referrals (a student/teacher/staff referring a new person to
    // join THIS institute) - separate from the platform-level referrals
    // block above.
    Route::get('/internal-referrals', [InternalReferralController::class, 'index'])->name('internal-referrals.index');
    Route::post('/internal-referrals/{id}/approve', [InternalReferralController::class, 'approve'])->name('internal-referrals.approve');
    Route::post('/internal-referrals/{id}/reject', [InternalReferralController::class, 'reject'])->name('internal-referrals.reject');

    // Institute admin's review/approve/pay queue for withdrawal requests.
    Route::get('/institute-withdrawals', [InstituteWithdrawalController::class, 'index'])->name('institute-withdrawals.index');
    Route::post('/institute-withdrawals/{id}/approve', [InstituteWithdrawalController::class, 'approve'])->name('institute-withdrawals.approve');
    Route::post('/institute-withdrawals/{id}/mark-paid', [InstituteWithdrawalController::class, 'markPaid'])->name('institute-withdrawals.mark-paid');
    Route::post('/institute-withdrawals/{id}/reject', [InstituteWithdrawalController::class, 'reject'])->name('institute-withdrawals.reject');

    // Institute admin's own withdrawal limits/methods override.
    Route::get('/institute-withdrawal-settings', [InstituteWithdrawalSettingController::class, 'index'])->name('institute-withdrawal-settings.index');
    Route::put('/institute-withdrawal-settings', [InstituteWithdrawalSettingController::class, 'update'])->name('institute-withdrawal-settings.update');
    Route::delete('/institute-withdrawal-settings', [InstituteWithdrawalSettingController::class, 'reset'])->name('institute-withdrawal-settings.reset');
});

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::resource('plans', PlanController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::post('institutes/{institute}/subscription/update', [InstituteController::class, 'updateSubscription'])->name('institutes.subscription.update');
    Route::post('institutes/{institute}/toggle-status', [InstituteController::class, 'toggleStatus'])->name('institutes.toggle-status');
    Route::resource('institutes', InstituteController::class);
    Route::resource('branches', BranchController::class)->middleware('plan.limit:branch');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});