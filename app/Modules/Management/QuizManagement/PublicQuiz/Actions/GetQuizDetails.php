<?php

namespace App\Modules\Management\QuizManagement\PublicQuiz\Actions;

use Carbon\Carbon;

class GetQuizDetails
{
    static $model = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($quizId)
    {
        try {
            $fields = [
                'id',
                'title',
                'description',
                'total_question',
                'exam_start_datetime',
                'exam_end_datetime',
                'total_mark',
                'pass_mark',
                'is_negative_marking',
                'negative_value',
                'slug'
            ];

            $quiz = self::$model::query()
                ->select($fields)
                ->where('id', $quizId)
                ->where('status', 'active')
                ->first();

            if (!$quiz) {
                return messageResponse('Quiz not found', [], 404, 'not_found');
            }

            return entityResponse([
                'quiz' => $quiz
            ]);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
