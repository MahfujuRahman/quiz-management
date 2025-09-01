<?php

use Illuminate\Support\Facades\Route;

// Quiz Participation Routes
Route::group(['prefix' => 'quiz-participation'], function () {
    Route::post('/register', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'register_for_quiz']);
    Route::post('/start', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'start_quiz_exam']);
    Route::post('/submit', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'submit_quiz']);
    Route::get('/result', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'get_participant_result']);
    Route::get('/export/{quizId}', [\App\Modules\Management\QuizManagement\QuizParticipation\Controller\Controller::class, 'export_results']);
});

