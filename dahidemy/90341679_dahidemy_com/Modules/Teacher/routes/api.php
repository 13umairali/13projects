<?php

use Illuminate\Support\Facades\Route;
use Modules\Teacher\Http\Controllers\API\BehaviorController;
use Modules\Teacher\Http\Controllers\API\ClassLessonController;
use Modules\Teacher\Http\Controllers\API\GamificationController;
use Modules\Teacher\Http\Controllers\API\PrayerController;
use Modules\Teacher\Http\Controllers\API\ResourceController;
use Modules\Teacher\Http\Controllers\API\TeacherController;

Route::middleware(['teacher', 'auth:api'])->group(function () {
    Route::get('teacher/my-profile', [TeacherController::class, 'my_profile']);
    Route::get('teacher/class-schedule', [TeacherController::class, 'class_schedule']);
    Route::get('teacher/assignments', [TeacherController::class, 'assignments']);
    Route::get('teacher/create-assignment', [TeacherController::class, 'create_assignment']);
    Route::post('teacher/store-assignment', [TeacherController::class, 'store_assignment']);
    Route::get('teacher/edit-assignment/{id}', [TeacherController::class, 'edit_assignment']);
    Route::get('teacher/assignment/{id}', [TeacherController::class, 'show_assignment']);
    Route::post('teacher/update-assignment/{id}', [TeacherController::class, 'update_assignment']);
    Route::get('teacher/destroy-assignment/{id}', [TeacherController::class, 'destroy_assignment']);

    // Core Setting
    Route::get('teacher/notices', [TeacherController::class, 'teacherNoticeGet']);
    Route::get('teacher/events', [TeacherController::class, 'teacherEventGet']);

    Route::apiResource('classlessons', ClassLessonController::class);
    Route::apiResource('prayers', PrayerController::class);
    Route::apiResource('behaviors', BehaviorController::class);
    Route::apiResource('gamifications', GamificationController::class);
    Route::apiResource('resources', ResourceController::class);
});
