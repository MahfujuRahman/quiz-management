<?php

namespace App\Modules\Management\QuizManagement\Quiz\Actions;

use Carbon\Carbon;

class SubmitQuizExam
{
    static $quizModel = \App\Modules\Management\QuizManagement\Quiz\Models\Model::class;
    static $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;

    public static function execute($request, $sessionToken)
    {
        try {
            $quizId = $request->input('quiz_id');
            $answers = $request->input('answers');
            $duration = $request->input('duration');
            $submitReason = $request->input('submit_reason', 'Normal completion');

            // Validate that the quiz exists and is active or processing
            $participation = self::$participationModel::query()
                ->where('quiz_id', $quizId)
                ->where('session_token', $sessionToken)
                ->whereIn('status', ['active', 'processing']) // Allow both active and processing status
                ->first();

            if (!$participation) {
                return messageResponse('Invalid session', [], 401, 'invalid_session');
            }

            // if ($participation->is_completed) {
            //     return messageResponse('Quiz already submitted', [], 400, 'already_submitted');
            // }

            // Get quiz details for marking
            $quiz = self::$quizModel::with('quiz_questions.quiz_question_options')
                ->where('id', $quizId)
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

            // Calculate marks
            $obtainedMarks = self::calculateMarks($quiz, $answers);
            $percentage = ($obtainedMarks / $quiz->total_mark) * 100;
            $isPassed = $obtainedMarks >= $quiz->pass_mark;


            // Update participation record
            $participation->update([
                'answers' => $answers,
                'obtained_marks' => $obtainedMarks,
                'percentage' => $percentage,
                'duration' => $duration,
                'submit_reason' => $submitReason,
                'submitted_at' =>  $now,
                'is_completed' => true,
                'is_passed' => $isPassed
            ]);

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

        $totalMarks = 0;
        $negativeMarking = $quiz->is_negative_marking;
        $negativeValue = $quiz->negative_value ?? 0;

        foreach ($quiz->quiz_questions as $question) {
            $questionId = $question->id;
            $questionMark = $question->mark;
            $userAnswers = $answers[$questionId] ?? [];

            if (empty($userAnswers)) {
                continue; // No answer given
            }

            // Get correct answers
            $correctAnswers = $question->quiz_question_options
                ->where('is_correct', true)
                ->pluck('id')
                ->toArray();

            if ($question->is_multiple) {
                // Multiple choice - all correct answers must be selected
                $userAnswerIds = array_map('intval', $userAnswers);
                $correctAnswerIds = array_map('intval', $correctAnswers);

                sort($userAnswerIds);
                sort($correctAnswerIds);

                if ($userAnswerIds === $correctAnswerIds) {
                    $totalMarks += $questionMark;
                } elseif ($negativeMarking) {
                    $totalMarks -= $negativeValue;
                }
            } else {
                // Single choice
                $userAnswer = intval($userAnswers[0]);
                if (in_array($userAnswer, $correctAnswers)) {
                    $totalMarks += $questionMark;
                } elseif ($negativeMarking) {
                    $totalMarks -= $negativeValue;
                }
            }
        }

        return max(0, $totalMarks); // Ensure marks don't go negative
    }
}
