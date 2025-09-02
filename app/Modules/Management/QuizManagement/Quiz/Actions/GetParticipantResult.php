<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

class GetParticipantResult
{
    static $quizModel = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($request, $sessionToken)
    {
        try {
            $quizId = $request->input('quiz_id');

            // Get quiz details
            $quiz = self::$quizModel::find($quizId);

            if (!$quiz) {
                return messageResponse('Quiz not found', [], 404, 'quiz_not_found');
            }

            // For now, return a sample result since we don't have participation records yet
            // In a real implementation, you would query the participation table
            return entityResponse([
                'participant' => [
                    'name' => 'Demo Participant',
                    'email' => 'demo@example.com',
                    'phone' => '+1234567890',
                    'organization' => 'Demo Organization'
                ],
                'quiz' => [
                    'title' => $quiz->title,
                    'total_mark' => $quiz->total_mark,
                    'pass_mark' => $quiz->pass_mark
                ],
                'result' => [
                    'obtained_marks' => 85,
                    'percentage' => 85.0,
                    'is_passed' => true,
                    'duration' => '25:30',
                    'submit_reason' => 'Normal completion',
                    'submitted_at' => now()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
