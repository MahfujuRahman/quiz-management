<?php

namespace App\Modules\Management\QuizManagement\QuizQuestion\Actions;

class UpdateData
{
    static $model = \App\Modules\Management\QuizManagement\QuizQuestion\Models\Model::class;

    public static function execute($request,$slug)
    {
        try {
            if (!$data = self::$model::query()->where('slug', $slug)->first()) {
                return messageResponse('Data not found...',$data, 404, 'error');
            }
            $requestData = $request->validated();
            // Update main question fields
            $data->update([
                'quiz_question_topic_id' => $requestData['quiz_question_topic_id'],
                'title' => $requestData['title'],
                'is_multiple' => $requestData['is_multiple'] ?? false,
                'session_year' => $requestData['session_year'] ?? null,
                'question_level' => $requestData['question_level'] ?? null,
                'mark' => $requestData['mark'] ?? 1,
            ]);

            // Update options: preserve existing data unless explicitly changed
            if (method_exists($data, 'quiz_question_options')) {
                if (isset($requestData['options']) && is_array($requestData['options'])) {
                    $OptionModel = \App\Modules\Management\QuizManagement\QuizQuestion\Models\QuizQuestionOptionModel::class;
                    $oldOptions = $data->quiz_question_options()->get();
                    $updatedOptionIds = [];

                    foreach ($requestData['options'] as $option) {
                        if ($option !== null && $option !== '' && isset($option['value']) && $option['value'] !== '') {
                            $imageValue = null;
                            
                            if (isset($option['id'])) {
                                // Update existing option
                                $existingOption = $oldOptions->where('id', $option['id'])->first();
                                if ($existingOption) {
                                    $updatedOptionIds[] = $option['id'];
                                    
                                    // Handle image
                                    if ($option['type'] === 'image') {
                                        if (isset($option['image']) && $option['image'] instanceof \Illuminate\Http\UploadedFile) {
                                            // New image uploaded - delete old and upload new
                                            if ($existingOption->image) {
                                                $imagePath = public_path($existingOption->image);
                                                if (file_exists($imagePath)) {
                                                    @unlink($imagePath);
                                                }
                                            }
                                            $imageValue = uploader($option['image'], 'uploads/quiz/question/options');
                                        } else {
                                            // No new image uploaded - keep existing image
                                            $imageValue = $existingOption->image;
                                        }
                                    } else {
                                        // Text option - no image
                                        $imageValue = null;
                                    }
                                    
                                    // Update the option
                                    $existingOption->update([
                                        'title' => $option['type'] === 'text' ? $option['value'] : $existingOption->title,
                                        'is_correct' => ($option['is_correct'] === '1' || $option['is_correct'] === 1 || $option['is_correct'] === true) ? 1 : 0,
                                        'image' => $imageValue,
                                    ]);
                                }
                            } else {
                                // Create new option
                                if ($option['type'] === 'image' && isset($option['image']) && $option['image'] instanceof \Illuminate\Http\UploadedFile) {
                                    $imageValue = uploader($option['image'], 'uploads/quiz/question/options');
                                }
                                $newOption = $OptionModel::query()->create([
                                    'quiz_question_id' => $data->id,
                                    'title' => $option['type'] === 'text' ? $option['value'] : null,
                                    'is_correct' => ($option['is_correct'] === '1' || $option['is_correct'] === 1 || $option['is_correct'] === true) ? 1 : 0,
                                    'image' => $imageValue,
                                ]);
                                $updatedOptionIds[] = $newOption->id;
                            }
                        }
                    }

                    // Delete options that were not included in the update
                    $optionsToDelete = $oldOptions->whereNotIn('id', $updatedOptionIds);
                    foreach ($optionsToDelete as $optionToDelete) {
                        if ($optionToDelete->image) {
                            $imagePath = public_path($optionToDelete->image);
                            if (file_exists($imagePath)) {
                                @unlink($imagePath);
                            }
                        }
                        $optionToDelete->delete();
                    }
                }
            }

            return messageResponse('Item updated successfully',$data, 201);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(),[], 500, 'server_error');
        }
    }
}