<?php

namespace App\Modules\Management\QuizManagement\QuizQuestionTopic\Actions;

class DestroyData
{
    static $model = \App\Modules\Management\QuizManagement\QuizQuestionTopic\Models\Model::class;
    static $QuizQuestion = \App\Modules\Management\QuizManagement\QuizQuestion\Models\Model::class;
    static $QuizQuestionOption = \App\Modules\Management\QuizManagement\QuizQuestion\Models\QuizQuestionOptionModel::class;

    

    public static function execute($slug)
    {
        try {

            if (!$data = self::$model::where('slug', $slug)->first()) {
                return messageResponse('Data not found...', $data, 404, 'error');
            }

            $QuizQuestion = self::$QuizQuestion::where('quiz_question_topic_id', $data->id)->get();

            foreach ($QuizQuestion as $question) {
                $question->forceDelete();
            }

            $QuizQuestionOption = self::$QuizQuestionOption::whereIn('quiz_question_id', $QuizQuestion->pluck('id'))->get();

            foreach ($QuizQuestionOption as $option) {
                $option->forceDelete();
            }

            $data->forceDelete();
            return messageResponse('Item Successfully deleted', [], 200, 'success');
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}