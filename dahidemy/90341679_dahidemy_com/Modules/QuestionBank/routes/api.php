<?php

use Illuminate\Support\Facades\Route;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankBoardController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankChapterController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankClassController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankDifficultyLevelController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankGroupController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankLevelController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankSessionController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankSourceController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankSubjectController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankSubSourceController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankTagController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankTestController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankTopicController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankTypeController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankYearController;
use Modules\QuestionBank\Http\Controllers\API\QuestionCategoryController;
use Modules\QuestionBank\Http\Controllers\API\QuestionController;
use Modules\QuestionBank\Http\Controllers\API\QuizController;
use Modules\QuestionBank\Http\Controllers\API\QuizTestController;

Route::middleware(['auth:api'])->group(
    function () {
        Route::apiResource('question-bank-classes', QuestionBankClassController::class);
        Route::apiResource('question-bank-groups', QuestionBankGroupController::class);
        Route::apiResource('question-bank-subjects', QuestionBankSubjectController::class);
        Route::apiResource('question-bank-chapters', QuestionBankChapterController::class);
        Route::apiResource('question-bank-topics', QuestionBankTopicController::class);
        Route::apiResource('question-bank-types', QuestionBankTypeController::class);
        Route::apiResource('question-bank-sources', QuestionBankSourceController::class);
        Route::apiResource('question-bank-sub-sources', QuestionBankSubSourceController::class);
        Route::apiResource('question-bank-tags', QuestionBankYearController::class);
        Route::apiResource('question-bank-years', QuestionBankYearController::class);
        Route::apiResource('question-bank-boards', QuestionBankBoardController::class);
        Route::apiResource('question-bank-tags', QuestionBankTagController::class);
        Route::apiResource('question-bank-levels', QuestionBankLevelController::class);
        Route::apiResource('question-bank-sessions', QuestionBankSessionController::class);
        Route::apiResource('question-bank-difficulty-levels', QuestionBankDifficultyLevelController::class);
        Route::apiResource('question-bank-tests', QuestionBankTestController::class);

        Route::get('question-categories-wise-subjects', [QuestionCategoryController::class, 'getSubjectsByCategory']);
        Route::apiResource('question-categories', QuestionCategoryController::class);
        Route::apiResource('questions', QuestionController::class);
        Route::put('/quizzes/{id}/questions', [QuizController::class, 'updateQuestions']);
        Route::apiResource('quizzes', QuizController::class);
        Route::apiResource('quiz-test', QuizTestController::class);
    }
);
