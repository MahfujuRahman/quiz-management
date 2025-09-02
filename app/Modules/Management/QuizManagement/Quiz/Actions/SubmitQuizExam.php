<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

use Carbon\Carbon;

class SubmitQuizExam
{
    static $quizModel = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;

    public static function execute($request, $sessionToken)
    {
        try {
            $quizId = $request->input('quiz_id');
            $answers = $request->input('answers');
            $duration = $request->input('duration');
            $submitReason = $request->input('submit_reason', 'Normal completion');

            // Validate that the quiz exists and is active
            $quiz = self::$quizModel::query()
                ->where('id', $quizId)
                ->where('status', 'active')
                ->first();

            if (!$quiz) {
                return messageResponse('Quiz not found', [], 404, 'quiz_not_found');
            }

            // Check if quiz is still within time bounds
            $now = Carbon::now();
            $endTime = Carbon::parse($quiz->exam_end_datetime);

            if ($now->isAfter($endTime)) {
                return messageResponse('Quiz has already ended', [], 400, 'quiz_ended');
            }

            // Calculate marks (simplified for now)
            $obtainedMarks = self::calculateMarks($quiz, $answers);
            $percentage = ($obtainedMarks / $quiz->total_mark) * 100;
            $isPassed = $obtainedMarks >= $quiz->pass_mark;

            // Return success response
            return entityResponse([
                'message' => 'Quiz submitted successfully',
                'obtained_marks' => $obtainedMarks,
                'total_marks' => $quiz->total_mark,
                'percentage' => round($percentage, 2),
                'is_passed' => $isPassed,
                'pass_mark' => $quiz->pass_mark,
                'duration' => $duration,
                'submit_reason' => $submitReason
            ]);

        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }

    private static function calculateMarks($quiz, $answers)
    {
        // Simple calculation for demo purposes
        $totalQuestions = $quiz->total_question;
        $answeredQuestions = count($answers);
        
        // For demo, assume 70% correctness
        $estimatedCorrect = $answeredQuestions * 0.7;
        $markPerQuestion = $quiz->total_mark / $totalQuestions;
        
        return round($estimatedCorrect * $markPerQuestion, 2);
    }
}
