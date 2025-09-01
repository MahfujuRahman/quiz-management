<?php

namespace App\Modules\Management\QuizManagement\PublicQuiz\Controller;

use Illuminate\Http\Request;

class Controller extends \App\Http\Controllers\Controller
{
    public function get_public_quizzes(Request $request)
    {
        return \App\Modules\Management\QuizManagement\PublicQuiz\Actions\GetPublicQuizzes::execute();
    }

    public function get_quiz_details(Request $request, $id)
    {
        return \App\Modules\Management\QuizManagement\PublicQuiz\Actions\GetQuizDetails::execute($id);
    }

    public function validate_quiz_code(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        return \App\Modules\Management\QuizManagement\PublicQuiz\Actions\ValidateQuizCode::execute($request);
    }
}
