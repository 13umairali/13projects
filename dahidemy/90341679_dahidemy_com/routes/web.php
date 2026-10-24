<?php

use App\Http\Controllers\WEB\InstallController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'index'])->name('index')->middleware('checkInstallation');

// ---------------- Installation Process ----------------- //
Route::prefix('install')->middleware('checkInstallationStatus')->group(function () {
    // 1️⃣ Start Installation
    Route::get('/', [InstallController::class, 'index'])->name('install.start');
    Route::get('start', [InstallController::class, 'index']);
    // 2️⃣ Requirements Check
    Route::get('requirements', [InstallController::class, 'requirements'])->name('install.requirements');
    // 3️⃣ Permissions Check
    Route::get('permissions', [InstallController::class, 'permissions'])->name('install.permissions');
    // 4️⃣ Purchase Key / Envato Validation
    Route::get('step-4', [InstallController::class, 'keyWorld'])->name('install.purchase_key');
    Route::post('validate', [InstallController::class, 'validateInput'])->name('install.validate');
    // 5️⃣ Database Setup
    Route::get('database', [InstallController::class, 'showDatabaseForm'])->name('install.database');
    Route::post('check-database', [InstallController::class, 'checkDatabase'])->name('install.check_database');
    Route::post('database/save', [InstallController::class, 'saveDatabase'])->name('install.database.save');
    // 6️⃣ Installation / Migration & Seed
    Route::any('installation', [InstallController::class, 'installation'])->name('install.installation');
});

// ---------------- End Installation ----------------- //
// 7️⃣ Complete
Route::get('install/complete', [InstallController::class, 'complete'])->name('install.complete');

// Upgrade Module — legacy route redirects to new System Update manager
Route::middleware(['auth'])->group(function () {
    Route::get('/upgrade', fn() => redirect()->route('system.update.index'))->name('upgrade.index');
    Route::post('/upgrade', fn() => redirect()->route('system.update.index'));
});

//
Route::get('/page/{slug}', [WebsiteController::class, 'show'])->name('website.page');
Route::get('/student-vital/{encodedData}', [WebsiteController::class, 'vital'])->name('student-vital');
// SAAS Admin "login as institute": consumes the single-use link minted by POST api/institutes/{id}/impersonate.
Route::get('impersonate/{token}', [\App\Http\Controllers\WEB\ImpersonationController::class, 'consume'])
    ->middleware('throttle:20,1')->name('impersonation.consume');
Route::post('impersonate-leave', [\App\Http\Controllers\WEB\ImpersonationController::class, 'leave'])
    ->middleware('auth')->name('impersonation.leave');
Route::get('institute-cache-clear', [WebsiteController::class, 'instituteCacheClear'])->name('institute.cache.clear')->middleware('auth');
