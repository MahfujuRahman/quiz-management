<?php

namespace App\Modules\Management\QuizManagement\QuizQuestion\Actions;

class StoreData
{
    static $model = \App\Modules\Management\QuizManagement\QuizQuestion\Models\Model::class;
    static $QuizQuestionOptionModel = \App\Modules\Management\QuizManagement\QuizQuestion\Models\QuizQuestionOptionModel::class;

    public static function execute($request)
    {
        try {
            $requestData = $request->validated();
            // Create the main question
            $question = self::$model::query()->create([
                'quiz_question_topic_id' => $requestData['quiz_question_topic_id'],
                'title' => $requestData['title'],
                'is_multiple' => $requestData['is_multiple'] ?? false,
                'session_year' => $requestData['session_year'] ?? null,
                'question_level' => $requestData['question_level'] ?? null,
                'mark' => $requestData['mark'] ?? 1,
            ]);
            // Create options if present
            if ($question && isset($requestData['options']) && is_array($requestData['options'])) {
                foreach ($requestData['options'] as $option) {
                    if ($option !== null && $option !== '' && isset($option['value']) && $option['value'] !== '') {
                        $imageValue = null;
                        if ($option['type'] === 'image' && isset($option['image']) && $option['image'] instanceof \Illuminate\Http\UploadedFile) {
                            // Use uploader for the uploaded file
                            $imageValue = uploader($option['image'], 'uploads/quiz/question/options');
                        }
                        self::$QuizQuestionOptionModel::query()->create([
                            'quiz_question_id' => $question->id,
                            'title' => $option['type'] === 'text' ? $option['value'] : null,
                            'is_correct' => $option['is_correct'] ?? false,
                            'image' => $imageValue,
                        ]);
                    }
                }
            }
            return messageResponse('Item added successfully', $question, 201);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
