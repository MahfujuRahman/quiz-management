<?php

namespace App\Modules\Management\QuizManagement\QuizQuestion\Actions;

class DestroyData
{
    static $model = \App\Modules\Management\QuizManagement\QuizQuestion\Models\Model::class;

    public static function execute($slug)
    {
        try {

            if (!$data = self::$model::where('slug', $slug)->first()) {
                return messageResponse('Data not found...', $data, 404, 'error');
            }

            // Delete related images for options
            $OptionModel = \App\Modules\Management\QuizManagement\QuizQuestion\Models\QuizQuestionOptionModel::class;
            $oldOptions = $data->quiz_question_options()->get();

            foreach ($oldOptions as $option) {
                if ($option->image && file_exists(public_path($option->image))) {
                    @unlink(public_path($option->image));
                }
            }
            $data->forceDelete();
            
            return messageResponse('Item Successfully deleted', [], 200, 'success');
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}