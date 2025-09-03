<?php

namespace App\Modules\Management\QuizManagement\Quiz\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as ControllersController;
use App\Modules\Management\QuizManagement\Quiz\Actions\StoreData;
use App\Modules\Management\QuizManagement\Quiz\Actions\GetAllData;
use App\Modules\Management\QuizManagement\Quiz\Actions\ImportData;
use App\Modules\Management\QuizManagement\Quiz\Actions\SoftDelete;
use App\Modules\Management\QuizManagement\Quiz\Actions\UpdateData;
use App\Modules\Management\QuizManagement\Quiz\Actions\BulkActions;
use App\Modules\Management\QuizManagement\Quiz\Actions\DestroyData;
use App\Modules\Management\QuizManagement\Quiz\Actions\RestoreData;
use App\Modules\Management\QuizManagement\Quiz\Actions\UpdateStatus;
use App\Modules\Management\QuizManagement\Quiz\Actions\GetSingleData;
use App\Modules\Management\QuizManagement\Quiz\Actions\QuizQuestions;
use App\Modules\Management\QuizManagement\Quiz\Actions\RegisterForQuiz;
use App\Modules\Management\QuizManagement\Quiz\Actions\GetPublicQuizzes;
use App\Modules\Management\QuizManagement\Quiz\Actions\ValidateQuizCode;
use App\Modules\Management\QuizManagement\Quiz\Actions\StartQuizExam;
use App\Modules\Management\QuizManagement\Quiz\Actions\SubmitQuizExam;
use App\Modules\Management\QuizManagement\Quiz\Actions\GetParticipantResult;
use App\Modules\Management\QuizManagement\Quiz\Actions\ExportQuizResults;
use App\Modules\Management\QuizManagement\Quiz\Validations\DataStoreValidation;
use App\Modules\Management\QuizManagement\Quiz\Validations\BulkActionsValidation;


class Controller extends ControllersController
{

    public function index()
    {

        $data = GetAllData::execute();
        return $data;
    }

    public function store(DataStoreValidation $request)
    {
        $data = StoreData::execute($request);
        return $data;
    }

    public function show($slug)
    {
        $data = GetSingleData::execute($slug);
        return $data;
    }

    public function update(DataStoreValidation $request, $slug)
    {
        $data = UpdateData::execute($request, $slug);
        return $data;
    }
    public function updateStatus()
    {
        $data = UpdateStatus::execute();
        return $data;
    }

    public function softDelete()
    {
        $data = SoftDelete::execute();
        return $data;
    }
    public function destroy($slug)
    {
        $data = DestroyData::execute($slug);
        return $data;
    }
    public function restore()
    {
        $data = RestoreData::execute();
        return $data;
    }
    public function import()
    {
        $data = ImportData::execute();
        return $data;
    }
    public function bulkAction(BulkActionsValidation $request)
    {
        $data = BulkActions::execute();
        return $data;
    }

    public function quiz_questions()
    {
        $data = QuizQuestions::execute();
        return $data;
    }

    public function get_public_quizzes(Request $request)
    {

        $data = GetPublicQuizzes::execute();
        return $data;
    }
    public function validate_quiz_code()
    {
        $data = ValidateQuizCode::execute();
        return $data;
    }

    public function register_for_quiz(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'organization' => 'nullable|string|max:255'
        ]);

        $data = RegisterForQuiz::execute($request);
        return $data;
    }

    public function start_quiz_exam(Request $request)
    {
        $sessionToken = $request->header('Authorization') ? str_replace('Bearer ', '', $request->header('Authorization')) : null;
        
        if (!$sessionToken) {
            return messageResponse('Session token required', [], 401, 'unauthorized');
        }

        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id'
        ]);

        return StartQuizExam::execute($request, $sessionToken);
    }

    public function submit_quiz(Request $request)
    {
        $sessionToken = $request->header('Authorization') ? str_replace('Bearer ', '', $request->header('Authorization')) : null;
        
        if (!$sessionToken) {
            return messageResponse('Session token required', [], 401, 'unauthorized');
        }

        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'answers' => 'nullable|array',
            'duration' => 'required|integer|min:0',
            'submit_reason' => 'sometimes|string|max:255'
        ]);

        $data = SubmitQuizExam::execute($request, $sessionToken);

        
        return $data;
    }

    public function export_results(Request $request, $quizId)
    {
        return ExportQuizResults::execute($request, $quizId);
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

        return GetParticipantResult::execute($request, $sessionToken);
    }
}
