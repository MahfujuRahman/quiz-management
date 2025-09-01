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
use App\Modules\Management\QuizManagement\Quiz\Actions\GetPublicQuizzes;
use App\Modules\Management\QuizManagement\Quiz\Actions\ValidateQuizCode;
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
        $data = BulkActions::execute($request);
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
}
