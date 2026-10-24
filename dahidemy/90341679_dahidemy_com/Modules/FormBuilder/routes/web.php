<?php

use Illuminate\Support\Facades\Route;
use Modules\FormBuilder\Http\Controllers\WEB\FormBuilderController;
use Modules\FormBuilder\Http\Controllers\WEB\PublicFormController;

/*
|--------------------------------------------------------------------------
| Admin — manage forms (auth-protected, same pattern as Modules/Frontend)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth'], 'prefix' => 'dynamic-forms', 'as' => 'formbuilder.forms.'], function () {
    Route::get('/', [FormBuilderController::class, 'index'])->name('index');
    Route::get('create', [FormBuilderController::class, 'create'])->name('create');
    Route::post('/', [FormBuilderController::class, 'store'])->name('store');
    Route::get('{id}/edit', [FormBuilderController::class, 'edit'])->name('edit');
    Route::put('{id}', [FormBuilderController::class, 'update'])->name('update');
    Route::delete('{id}', [FormBuilderController::class, 'destroy'])->name('destroy');
    Route::patch('{id}/toggle-status', [FormBuilderController::class, 'toggleStatus'])->name('toggle-status');
    Route::patch('{id}/toggle-homepage', [FormBuilderController::class, 'toggleHomepage'])->name('toggle-homepage');
    Route::get('{id}/submissions', [FormBuilderController::class, 'submissions'])->name('submissions');
    Route::get('{id}/submissions/{submissionId}', [FormBuilderController::class, 'showSubmission'])->name('submissions.show');
    Route::post('{id}/submissions/{submissionId}/approve', [FormBuilderController::class, 'approveSubmission'])->name('submissions.approve');
    Route::post('{id}/submissions/{submissionId}/reject', [FormBuilderController::class, 'rejectSubmission'])->name('submissions.reject');
});

/*
|--------------------------------------------------------------------------
| Public — view + submit a published form by slug
|--------------------------------------------------------------------------
*/
Route::get('forms', [PublicFormController::class, 'index'])->name('public-form.index');
Route::get('forms/{slug}', [PublicFormController::class, 'show'])->name('public-form.show');
Route::post('forms/{slug}', [PublicFormController::class, 'submit'])->name('public-form.submit');
