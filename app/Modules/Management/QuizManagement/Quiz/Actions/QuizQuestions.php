<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

class QuizQuestions
{
    static $model = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute()
    {
        try {

            $pageLimit = request()->input('limit') ?? 10;
            $orderByColumn = request()->input('sort_by_col') ?? 'id';
            $orderByType = request()->input('sort_type') ?? 'desc';
            $status = request()->input('status') ?? 'active';

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

            $with = [
                'quiz_questions:id,quiz_question_topic_id,title,question_level,mark,is_multiple,slug',
                'quiz_questions.quiz_question_options:id,quiz_question_id,title,is_correct,image,slug',
            ];

            $condition = [];

            $data = self::$model::query();

            $data = $data
                ->with($with)
                ->select($fields)
                ->where($condition)
                ->where('status', $status)
                ->orderBy($orderByColumn, $orderByType)
                ->paginate($pageLimit);

            return entityResponse([
                ...$data->toArray(),
            ]);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
