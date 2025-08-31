<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StoreData
{
    static $model = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($request)
    {

        try {
            // Use all() instead of validated() to include quiz_questions
            $requestData = $request->all();
            
            // Remove quiz_questions from main data to avoid column error
            $quizQuestions = $requestData['quiz_questions'] ?? [];
            unset($requestData['quiz_questions']);

            if ($data = self::$model::query()->create($requestData)) {
                // Debug: Check if quiz was created
                Log::info('Quiz created with ID: ' . $data->id);
                
                // Refresh the model to ensure relationships work
                $data = $data->fresh();
                
                // Debug: Check quiz questions to sync
                Log::info('Quiz questions to sync: ', $quizQuestions);
                
                // Try manual DB insert as alternative to sync
                if (!empty($quizQuestions)) {
                    try {
                        // First try sync method
                        $syncResult = $data->quiz_questions()->sync($quizQuestions);
                        Log::info('Sync result: ', $syncResult);
                        
                        // If sync doesn't work, try manual insert
                        if (empty($syncResult['attached'])) {
                            Log::info('Sync failed, trying manual insert...');
                            foreach ($quizQuestions as $questionId) {
                                DB::table('quiz_quiz_questions')->insert([
                                    'quiz_id' => $data->id,
                                    'quiz_question_id' => $questionId,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                            Log::info('Manual insert completed');
                        }
                    } catch (\Exception $syncError) {
                        Log::error('Sync error: ' . $syncError->getMessage());
                        throw $syncError;
                    }
                }
                
                return messageResponse('Item added successfully', $data, 201);
            }
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
