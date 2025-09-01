<?php

use Illuminate\Support\Facades\Route;

// Public Quiz Routes (No Authentication Required)
Route::group(['prefix' => 'public-quiz'], function () {
    Route::get('/list', [\App\Modules\Management\QuizManagement\PublicQuiz\Controller\Controller::class, 'get_public_quizzes']);
    Route::get('/details/{id}', [\App\Modules\Management\QuizManagement\PublicQuiz\Controller\Controller::class, 'get_quiz_details']);
    Route::post('/validate-code', [\App\Modules\Management\QuizManagement\PublicQuiz\Controller\Controller::class, 'validate_quiz_code']);
});
