<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

class ValidateQuizCode
{
    static $model = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute()
    {
        $quizId = request()->input('quiz');
        
        try {
            $quiz = self::$model::query()
                ->select(['id', 'title', 'exam_start_datetime', 'exam_end_datetime', 'status','slug'])
                ->where('slug', $quizId)
                ->where('status', 'active')
                ->first();

            if (!$quiz) {
                return messageResponse('Invalid quiz code', [], 404, 'invalid_code');
            }

            return entityResponse([
                'success' => true,
                'quiz' => $quiz
            ]);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
