<?php

use Illuminate\Support\Facades\Route;
use Modules\ResultPublication\Http\Controllers\WEB\AdminResultReviewController;
use Modules\ResultPublication\Http\Controllers\WEB\ParentResultController;
use Modules\ResultPublication\Http\Controllers\WEB\StudentResultController;
use Modules\ResultPublication\Http\Controllers\WEB\TeacherResultController;

/*
|--------------------------------------------------------------------------
| Teacher — submit results for admin approval
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['teacher'], 'prefix' => 'teacher/results', 'as' => 'resultpublication.teacher.'], function () {
    Route::get('/', [TeacherResultController::class, 'index'])->name('index');
    Route::get('create', [TeacherResultController::class, 'create'])->name('create');
    Route::post('/', [TeacherResultController::class, 'store'])->name('store');
    Route::get('{id}/edit', [TeacherResultController::class, 'edit'])->name('edit');
    Route::put('{id}', [TeacherResultController::class, 'update'])->name('update');
    Route::delete('{id}', [TeacherResultController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Admin — review, approve, reject
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth'], 'prefix' => 'admin/results', 'as' => 'resultpublication.admin.'], function () {
    Route::get('/', [AdminResultReviewController::class, 'index'])->name('index');
    Route::get('create', [AdminResultReviewController::class, 'create'])->name('create');
    Route::post('/', [AdminResultReviewController::class, 'store'])->name('store');
    Route::get('{id}/edit', [AdminResultReviewController::class, 'edit'])->name('edit');
    Route::put('{id}', [AdminResultReviewController::class, 'update'])->name('update');
    Route::delete('{id}', [AdminResultReviewController::class, 'destroy'])->name('destroy');
    Route::get('{id}', [AdminResultReviewController::class, 'show'])->name('show');
    Route::post('{id}/approve', [AdminResultReviewController::class, 'approve'])->name('approve');
    Route::post('{id}/reject', [AdminResultReviewController::class, 'reject'])->name('reject');
});

/*
|--------------------------------------------------------------------------
| Student — view their own published results
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['student']], function () {
    Route::get('student/my-results', [StudentResultController::class, 'index'])->name('resultpublication.student.index');
});

/*
|--------------------------------------------------------------------------
| Parent — view their children's published results
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'parent']], function () {
    Route::get('parent/child-results', [ParentResultController::class, 'index'])->name('resultpublication.parent.index');
});
