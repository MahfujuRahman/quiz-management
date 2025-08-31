<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateData
{
    static $model = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($request,$slug)
    {
        try {
            if (!$data = self::$model::query()->where('slug', $slug)->first()) {
                return messageResponse('Data not found...',$data, 404, 'error');
            }
            
            // Use all() instead of validated() to include quiz_questions
            $requestData = $request->all();
            
            // Remove quiz_questions from main data to avoid column error
            $quizQuestions = $requestData['quiz_questions'] ?? [];
            unset($requestData['quiz_questions']);
            
            // Update main quiz data
            $data->update($requestData);
            
            // Debug: Check quiz questions to sync
            Log::info('Quiz questions to sync (update): ', $quizQuestions);
            
            // Try manual DB insert as alternative to sync
            if (!empty($quizQuestions)) {
                try {
                    // First try sync method
                    $syncResult = $data->quiz_questions()->sync($quizQuestions);
                    Log::info('Sync result (update): ', $syncResult);
                    // If sync doesn't work, try manual insert
                    if (empty($syncResult['attached'])) {
                        Log::info('Sync failed, trying manual insert (update)...');
                        foreach ($quizQuestions as $questionId) {
                            DB::table('quiz_quiz_questions')->updateOrInsert([
                                'quiz_id' => $data->id,
                                'quiz_question_id' => $questionId
                            ], [
                                'updated_at' => now(),
                                'created_at' => now()
                            ]);
                        }
                        Log::info('Manual insert completed (update)');
                    }
                } catch (\Exception $syncError) {
                    Log::error('Sync error (update): ' . $syncError->getMessage());
                    throw $syncError;
                }
            } else {
                // If no questions provided, detach all
                $data->quiz_questions()->detach();
            }
            
            return messageResponse('Item updated successfully',$data, 201);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(),[], 500, 'server_error');
        }
    }
}