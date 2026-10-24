<?php

use Illuminate\Support\Facades\Route;
use Modules\Academic\Http\Controllers\API\ExamController;
use Modules\Examination\Http\Controllers\API\ExamMarkInputController;
use Modules\Examination\Http\Controllers\API\ExamResultApiController;
use Modules\Examination\Http\Controllers\API\RemarksConfigController;
use Modules\Examination\Http\Controllers\API\SemesterExamSettingController;

Route::middleware('auth:api')->group(function () {
    Route::get('short-code', [SemesterExamSettingController::class, 'shortCode']);
    Route::post('exam-code-store', [SemesterExamSettingController::class, 'examCodeStore']);
    Route::post('exam-code-update', [SemesterExamSettingController::class, 'examCodeUpdate']);
    Route::get('grades', [SemesterExamSettingController::class, 'grades']);
    Route::post('exam-grade-store', [SemesterExamSettingController::class, 'examGradeStore']);
    Route::post('exam-grade-update', [SemesterExamSettingController::class, 'examGradeUpdate']);
    Route::get('merit-process-type', [SemesterExamSettingController::class, 'meritProcessType']);
    Route::get('class-exam', [SemesterExamSettingController::class, 'classExam']);
    Route::get('exam-code', [SemesterExamSettingController::class, 'examCode']);
    Route::get('exam-grade', [SemesterExamSettingController::class, 'examGrade']);
    Route::apiResource('exams', ExamController::class);

    // Exam-Module
    Route::apiResource('remarks-config', RemarksConfigController::class);
    Route::post('exam-assign-store', [ExamMarkInputController::class, 'examAssignStore']);
    Route::get('semester-exam-settings-mark-config', [ExamMarkInputController::class, 'markConfig']);
    Route::get('grand-final-mark-percentage/{class_id}', [ExamMarkInputController::class, 'grandFinalMarkPercentage']);
    Route::post('general-exam-store', [ExamMarkInputController::class, 'generalExamStore']);
    Route::post('grand-final-exam-store', [ExamMarkInputController::class, 'grandFinalExamStore']);
    Route::get('mark-input-section-wise-class/{class_id}', [ExamMarkInputController::class, 'markInputSectionWiseClass']);
    Route::post('mark-store-section-wise', [ExamMarkInputController::class, 'markStoreSectionWise']);
    Route::get('exam-results', [ExamMarkInputController::class, 'examResult']);
    Route::get('exam-result-card', [ExamResultApiController::class, 'resultCard']);
    Route::get('annual-result-card', [ExamResultApiController::class, 'annualResultCard']);
    Route::get('tabulation-sheet', [ExamResultApiController::class, 'tabulation']);
    Route::get('empty-mark-sheet', [ExamResultApiController::class, 'emptyMarkSheet']);
    Route::get('result-card-settings', [ExamResultApiController::class, 'resultCardSettings']);
    Route::post('result-card-settings', [ExamResultApiController::class, 'updateResultCardSettings']);
    Route::get('assessment-domains', [ExamResultApiController::class, 'assessmentDomains']);
    Route::post('assessment-domains', [ExamResultApiController::class, 'storeAssessmentDomain']);
    Route::post('assessment-items', [ExamResultApiController::class, 'storeAssessmentItem']);
    Route::post('student-assessments', [ExamResultApiController::class, 'storeStudentAssessments']);
    Route::get('online-exams', [ExamResultApiController::class, 'onlineExams']);
    Route::post('online-exams', [ExamResultApiController::class, 'storeOnlineExam']);
    Route::post('online-exams/{id}/questions', [ExamResultApiController::class, 'storeOnlineExamQuestion']);
    Route::get('online-exams/{id}/transfer-preview', [ExamResultApiController::class, 'transferPreview']);
    Route::post('online-exams/{id}/transfer', [ExamResultApiController::class, 'transferMarks']);
});
