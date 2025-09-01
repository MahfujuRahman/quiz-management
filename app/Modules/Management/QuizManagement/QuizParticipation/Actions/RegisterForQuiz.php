<?php

namespace App\Modules\Management\QuizManagement\QuizParticipation\Actions;

use Carbon\Carbon;
use Illuminate\Support\Str;

class RegisterForQuiz
{
    static $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;
    static $quizModel = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($request)
    {
        try {
            $quizId = $request->input('quiz_id');
            $studentInfo = $request->input('student_info');

            // Validate quiz exists and is active
            $quiz = self::$quizModel::query()
                ->where('id', $quizId)
                ->where('status', 'active')
                ->first();

            if (!$quiz) {
                return messageResponse('Quiz not found or inactive', [], 404, 'quiz_not_found');
            }

            // Check if quiz has started and not ended
            $now = Carbon::now();
            $startTime = Carbon::parse($quiz->exam_start_datetime);
            $endTime = Carbon::parse($quiz->exam_end_datetime);

            if ($now->isBefore($startTime)) {
                return messageResponse('Quiz has not started yet', [], 400, 'quiz_not_started');
            }

            if ($now->isAfter($endTime)) {
                return messageResponse('Quiz has already ended', [], 400, 'quiz_ended');
            }

            // Check if user already registered for this quiz (based on mobile number)
            $existingParticipation = self::$participationModel::query()
                ->where('quiz_id', $quizId)
                ->whereJsonContains('student_info->mobile', $studentInfo['mobile'])
                ->first();

            if ($existingParticipation) {
                // Return existing session token
                return entityResponse([
                    'message' => 'Already registered',
                    'session_token' => $existingParticipation->session_token,
                    'participation_id' => $existingParticipation->id
                ]);
            }

            // Create new participation record
            $sessionToken = Str::random(64);
            
            $participation = self::$participationModel::create([
                'quiz_id' => $quizId,
                'session_token' => $sessionToken,
                'student_info' => $studentInfo,
                'started_at' => $now,
                'is_completed' => false,
                'status' => 'active'
            ]);

            return entityResponse([
                'message' => 'Registration successful',
                'session_token' => $sessionToken,
                'participation_id' => $participation->id
            ]);

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
