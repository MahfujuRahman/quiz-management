<?php

namespace App\Modules\Management\QuizManagement\QuizParticipation\Actions;

use Carbon\Carbon;

class StartQuizExam
{
    static $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;
    static $quizModel = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($quizId, $sessionToken)
    {
        try {
            // Validate session token
            $participation = self::$participationModel::query()
                ->where('quiz_id', $quizId)
                ->where('session_token', $sessionToken)
                ->where('status', 'active')
                ->first();

            if (!$participation) {
                return messageResponse('Invalid session', [], 401, 'invalid_session');
            }

            // Get quiz with questions
            $quiz = self::$quizModel::query()
                ->with([
                    'quiz_questions' => function ($query) {
                        $query->select([
                            'id', 'title', 'question_level', 'mark', 'is_multiple'
                        ])->inRandomOrder(); // Randomize questions
                    },
                    'quiz_questions.quiz_question_options' => function ($query) {
                        $query->select([
                            'id', 'quiz_question_id', 'title', 'image'
                        ])->inRandomOrder(); // Randomize options
                    }
                ])
                ->where('id', $quizId)
                ->where('status', 'active')
                ->first();

            if (!$quiz) {
                return messageResponse('Quiz not found', [], 404, 'quiz_not_found');
            }

            // Check if quiz is still active
            $now = Carbon::now();
            $endTime = Carbon::parse($quiz->exam_end_datetime);

            if ($now->isAfter($endTime)) {
                return messageResponse('Quiz has ended', [], 400, 'quiz_ended');
            }

            // Check if already completed
            if ($participation->is_completed) {
                return messageResponse('Quiz already completed', [], 400, 'already_completed');
            }

            // Update started_at if not already set
            if (!$participation->started_at) {
                $participation->update(['started_at' => $now]);
            }

            return entityResponse([
                'quiz' => $quiz,
                'questions' => $quiz->quiz_questions,
                'student_info' => $participation->student_info,
                'completion_message' => 'আপনার উত্তরপত্র সফলভাবে জমা দেওয়া হয়েছে। ধন্যবাদ!'
            ]);

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
