<?php

namespace App\Modules\Management\QuizManagement\QuizParticipation\Actions;

class GetParticipantResult
{
    static $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;
    static $quizModel = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($request, $sessionToken)
    {
        try {
            $quizId = $request->input('quiz_id');

            // Find participation record
            $participation = self::$participationModel::where('quiz_id', $quizId)
                ->where('session_token', $sessionToken)
                ->where('is_completed', true)
                ->first();

            if (!$participation) {
                return messageResponse('Result not found', [], 404, 'not_found');
            }

            // Get quiz details
            $quiz = self::$quizModel::find($quizId);

            return entityResponse([
                'participant' => [
                    'name' => $participation->name,
                    'email' => $participation->email,
                    'phone' => $participation->phone,
                    'organization' => $participation->organization
                ],
                'quiz' => [
                    'title' => $quiz->title,
                    'total_mark' => $quiz->total_mark,
                    'pass_mark' => $quiz->pass_mark
                ],
                'result' => [
                    'obtained_marks' => $participation->obtained_marks,
                    'percentage' => round($participation->percentage, 2),
                    'is_passed' => $participation->is_passed,
                    'duration' => $participation->duration,
                    'submit_reason' => $participation->submit_reason,
                    'submitted_at' => $participation->submitted_at ? $participation->submitted_at->format('Y-m-d H:i:s') : null
                ]
            ]);

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
