<?php

namespace App\Modules\Management\QuizManagement\QuizParticipation\Controller;

use Illuminate\Http\Request;

class Controller extends \App\Http\Controllers\Controller
{

    public function start_quiz_exam(Request $request)
    {
        $sessionToken = $request->header('Authorization') ? str_replace('Bearer ', '', $request->header('Authorization')) : null;
        
        if (!$sessionToken) {
            return messageResponse('Session token required', [], 401, 'unauthorized');
        }

        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id'
        ]);

        return \App\Modules\Management\QuizManagement\QuizParticipation\Actions\StartQuizExam::execute($request, $sessionToken);
    }

    public function submit_quiz(Request $request)
    {
        $sessionToken = $request->header('Authorization') ? str_replace('Bearer ', '', $request->header('Authorization')) : null;
        
        if (!$sessionToken) {
            return messageResponse('Session token required', [], 401, 'unauthorized');
        }

        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'answers' => 'required|array',
            'duration' => 'required|integer|min:0',
            'submit_reason' => 'sometimes|string|max:255'
        ]);

        return \App\Modules\Management\QuizManagement\QuizParticipation\Actions\SubmitQuizExam::execute($request, $sessionToken);
    }

    public function export_results(Request $request, $quizId)
    {
        return \App\Modules\Management\QuizManagement\QuizParticipation\Actions\ExportQuizResults::execute($request, $quizId);
    }

    public function get_participant_result(Request $request)
    {
        $sessionToken = $request->header('Authorization') ? str_replace('Bearer ', '', $request->header('Authorization')) : null;
        
        if (!$sessionToken) {
            return messageResponse('Session token required', [], 401, 'unauthorized');
        }

        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id'
        ]);

        return \App\Modules\Management\QuizManagement\QuizParticipation\Actions\GetParticipantResult::execute($request, $sessionToken);
    }
}
