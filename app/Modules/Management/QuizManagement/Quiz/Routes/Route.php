<?php

use App\Modules\Management\QuizManagement\Quiz\Controller\Controller;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Route::prefix('v1')->middleware('auth:api')->group(function () {
    Route::prefix('quizzes')->group(function () {
        Route::get('quiz_questions', [Controller::class, 'quiz_questions']);
        Route::post('start_exam', [Controller::class, 'start_quiz_exam']);
        Route::post('submit_exam', [Controller::class, 'submit_quiz']);
        Route::get('result', [Controller::class, 'get_participant_result']);
        Route::get('export/{quizId}', [Controller::class, 'export_results']);
        Route::get('', [Controller::class, 'index']);
        Route::get('{slug}', [Controller::class, 'show']);
        Route::post('store', [Controller::class, 'store']);
        Route::post('update/{slug}', [Controller::class, 'update']);
        Route::post('update-status', [Controller::class, 'updateStatus']);
        Route::post('soft-delete', [Controller::class, 'softDelete']);
        Route::post('destroy/{slug}', [Controller::class, 'destroy']);
        Route::post('restore', [Controller::class, 'restore']);
        Route::post('import', [Controller::class, 'import']);
        Route::post('bulk-action', [Controller::class, 'bulkAction']);
    });

    // Public Quiz Routes (No Authentication Required)
    Route::group(['prefix' => 'public-quizzes'], function () {
        Route::get('', [Controller::class, 'get_public_quizzes']);
        Route::post('/validate-code', [Controller::class, 'validate_quiz_code']);
        Route::post('/register', [Controller::class, 'register_for_quiz']);
        // Route::get('/details/{id}', [\App\Modules\Management\QuizManagement\PublicQuiz\Controller\Controller::class, 'get_quiz_details']);
    });
});



// Quiz Participation Routes
// Route::group(['prefix' => 'quiz-participation'], function () {
//     Route::post('/register', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'register_for_quiz']);
//     Route::post('/start', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'start_quiz_exam']);
//     Route::post('/submit', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'submit_quiz']);
//     Route::get('/result', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'get_participant_result']);
//     Route::get('/export/{quizId}', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'export_results']);
// });
