<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

use Carbon\Carbon;

class GetPublicQuizzes
{
    static $model = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute()
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
                'slug'
            ];

            $now = Carbon::now();

            $data = self::$model::query()
                ->select($fields)
                ->where('status', 'active')
                ->where('exam_end_datetime', '>', $now) // Only show non-expired quizzes
                ->orderBy('exam_start_datetime', 'asc')
                ->get();

            return entityResponse($data);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
