<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

class DestroyData
{
    static $model = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;
    static $course_modules_class_quizzes = \App\Modules\Management\CourseManagement\CourseModuleClassQuiz\Models\Model::class;
    static $quiz_quiz_questions = \App\Modules\Management\QuizManagement\Quiz\Models\QuizQuizQuestionModel::class;
    static $quiz_submissions = \App\Modules\Management\QuizManagement\Quiz\Models\QuizSubmissionModel::class;
    static $quiz_submission_results = \App\Modules\Management\QuizManagement\QuizSubmissionResult\Models\Model::class;

    public static function execute($slug)
    {
        
        try {

            if (!$data = self::$model::where('slug', $slug)->first()) {
                return messageResponse('Data not found...', $data, 404, 'error');
            }
            
            // Delete all related data for this quiz
            $quizId = $data->id;

            // Example: delete related questions
            self::$course_modules_class_quizzes::where('quiz_id', $quizId)->forceDelete();
            self::$quiz_quiz_questions::where('quiz_id', $quizId)->forceDelete();
            self::$quiz_submissions::where('quiz_id', $quizId)->forceDelete();
            self::$quiz_submission_results::where('quiz_id', $quizId)->forceDelete();

            // Add more related deletions as needed
            $data->forceDelete();
            
            return messageResponse('Item Successfully deleted', [], 200, 'success');
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}