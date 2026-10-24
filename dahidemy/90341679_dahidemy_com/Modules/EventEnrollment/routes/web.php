<?php

use Illuminate\Support\Facades\Route;
use Modules\EventEnrollment\Http\Controllers\WEB\EventEnrollmentController;
use Modules\EventEnrollment\Http\Controllers\WEB\EventPageController;

/*
|--------------------------------------------------------------------------
| Public event browsing + enrollment
|--------------------------------------------------------------------------
|
| Deliberately singular ("event") rather than "events" — the admin panel
| already has an authenticated Route::resource('events', ...) occupying the
| plural, bare /events* path (see Modules/Academic/routes/web.php). Using the
| same path here would mean every public visitor gets redirected to login
| before ever reaching the page.
*/
Route::prefix('event')->name('eventenrollment.')->group(function () {
    Route::get('/', [EventPageController::class, 'index'])->name('index');
    Route::get('{id}', [EventPageController::class, 'show'])->name('show');
    Route::post('{id}/enroll', [EventEnrollmentController::class, 'enroll'])->name('enroll');
    Route::get('{id}/checkout', [EventEnrollmentController::class, 'checkout'])->name('checkout');
    Route::post('{id}/checkout/{gateway}', [EventEnrollmentController::class, 'pay'])->name('checkout.pay');
    Route::get('{id}/thank-you/{ticket}', [EventEnrollmentController::class, 'thankYou'])->name('thankyou');
});
