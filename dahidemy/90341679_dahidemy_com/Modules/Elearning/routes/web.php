<?php

use Illuminate\Support\Facades\Route;
use Modules\Elearning\Http\Controllers\WEB\CourseEnrollmentController;
use Modules\Elearning\Http\Controllers\WEB\CoursePageController;

/*
|--------------------------------------------------------------------------
| Public course browsing + enrollment
|--------------------------------------------------------------------------
|
| This module previously had no web routes at all — every course lived only
| in the mobile app's API. These add a public website presence: browse,
| view a course, and enroll (free instantly, or paid through the same
| multi-gateway payment system used elsewhere on this site).
*/
Route::prefix('courses')->name('courses-page.')->group(function () {
    Route::get('/', [CoursePageController::class, 'index'])->name('index');
    Route::get('{slug}', [CoursePageController::class, 'show'])->name('show');

    // Enrollment requires a real account — Enrollment.user_id has no
    // "guest" concept, unlike Event/Form submissions. Guests hitting these
    // get redirected to login and back here automatically.
    Route::middleware('auth')->group(function () {
        Route::post('{slug}/enroll', [CourseEnrollmentController::class, 'enroll'])->name('enroll');
        Route::get('{slug}/checkout', [CourseEnrollmentController::class, 'checkout'])->name('checkout');
        Route::post('{slug}/checkout/{gateway}', [CourseEnrollmentController::class, 'pay'])->name('checkout.pay');
        Route::get('{slug}/thank-you', [CourseEnrollmentController::class, 'thankYou'])->name('thankyou');
    });
});

/*
|--------------------------------------------------------------------------
| Admin — course management (this module was previously API/mobile-app
| only; this is the first web admin UI for it). Scoped to core course
| fields plus chapters/lessons/features/FAQs — quiz/question creation is
| NOT included here, since Modules\QuestionBank is also 100% API-only and
| would be its own separate project to bring to the web.
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth'], 'prefix' => 'admin', 'as' => 'elearning.admin.'], function () {
    Route::resource('course-categories', \Modules\Elearning\Http\Controllers\WEB\AdminCourseCategoryController::class)
        ->names('categories')
        ->except(['show']);

    Route::resource('courses', \Modules\Elearning\Http\Controllers\WEB\AdminCourseController::class)
        ->names('courses')
        ->except(['show']);

    Route::prefix('courses/{course}/builder')->name('courses.')->group(function () {
        Route::get('/', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'show'])->name('builder');

        Route::post('chapters', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'storeChapter'])->name('chapters.store');
        Route::put('chapters/{chapter}', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'updateChapter'])->name('chapters.update');
        Route::delete('chapters/{chapter}', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'destroyChapter'])->name('chapters.destroy');

        Route::post('chapters/{chapter}/lessons', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'storeLesson'])->name('lessons.store');
        Route::put('lessons/{lesson}', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'updateLesson'])->name('lessons.update');
        Route::delete('lessons/{lesson}', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'destroyLesson'])->name('lessons.destroy');

        Route::post('features', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'storeFeature'])->name('features.store');
        Route::delete('features/{feature}', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'destroyFeature'])->name('features.destroy');

        Route::post('faqs', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'storeFaq'])->name('faqs.store');
        Route::delete('faqs/{faq}', [\Modules\Elearning\Http\Controllers\WEB\AdminCourseBuilderController::class, 'destroyFaq'])->name('faqs.destroy');
    });
});
