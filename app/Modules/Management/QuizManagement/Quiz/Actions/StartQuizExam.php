<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

use Carbon\Carbon;

class StartQuizExam
{
    static $quizModel = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($request, $sessionToken)
    {
        try {
            $quizId = $request->input('quiz_id');
            
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
            $startTime = Carbon::parse($quiz->exam_start_datetime);
            $endTime = Carbon::parse($quiz->exam_end_datetime);

            if ($now->isBefore($startTime)) {
                return messageResponse('Quiz has not started yet', [], 400, 'quiz_not_started');
            }

            if ($now->isAfter($endTime)) {
                return messageResponse('Quiz has ended', [], 400, 'quiz_ended');
            }

            return entityResponse([
                'quiz' => $quiz,
                'questions' => $quiz->quiz_questions,
                'student_info' => [
                    'name' => 'Test Student',
                    'email' => 'test@example.com'
                ],
                'completion_message' => 'আপনার উত্তরপত্র সফলভাবে জমা দেওয়া হয়েছে। ধন্যবাদ!'
            ]);

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
