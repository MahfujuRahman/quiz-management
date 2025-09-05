<?php

namespace App\Modules\Management\QuizManagement\Quiz\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Controller as ControllersController;
use App\Modules\Management\QuizManagement\Quiz\Actions\StoreData;
use App\Modules\Management\QuizManagement\Quiz\Actions\GetAllData;
use App\Modules\Management\QuizManagement\Quiz\Actions\ImportData;
use App\Modules\Management\QuizManagement\Quiz\Actions\SoftDelete;
use App\Modules\Management\QuizManagement\Quiz\Actions\UpdateData;
use App\Modules\Management\QuizManagement\Quiz\Actions\BulkActions;
use App\Modules\Management\QuizManagement\Quiz\Actions\DestroyData;
use App\Modules\Management\QuizManagement\Quiz\Actions\RestoreData;
use App\Modules\Management\QuizManagement\Quiz\Actions\UpdateStatus;
use App\Modules\Management\QuizManagement\Quiz\Others\SubmitQuizJob;
use App\Modules\Management\QuizManagement\Quiz\Actions\GetSingleData;
use App\Modules\Management\QuizManagement\Quiz\Actions\QuizQuestions;
use App\Modules\Management\QuizManagement\Quiz\Actions\StartQuizExam;
use App\Modules\Management\QuizManagement\Quiz\Actions\SubmitQuizExam;
use App\Modules\Management\QuizManagement\Quiz\Actions\RegisterForQuiz;
use App\Modules\Management\QuizManagement\Quiz\Actions\GetPublicQuizzes;
use App\Modules\Management\QuizManagement\Quiz\Actions\ValidateQuizCode;
use App\Modules\Management\QuizManagement\Quiz\Actions\ExportQuizResults;
use App\Modules\Management\QuizManagement\Quiz\Actions\GetParticipantResult;
use App\Modules\Management\QuizManagement\Quiz\Validations\DataStoreValidation;
use App\Modules\Management\QuizManagement\Quiz\Validations\BulkActionsValidation;


class Controller extends ControllersController
{

    public function index()
    {

        $data = GetAllData::execute();
        return $data;
    }

    public function store(DataStoreValidation $request)
    {
        $data = StoreData::execute($request);
        return $data;
    }

    public function show($slug)
    {
        $data = GetSingleData::execute($slug);
        return $data;
    }

    public function update(DataStoreValidation $request, $slug)
    {
        $data = UpdateData::execute($request, $slug);
        return $data;
    }
    public function updateStatus()
    {
        $data = UpdateStatus::execute();
        return $data;
    }

    public function softDelete()
    {
        $data = SoftDelete::execute();
        return $data;
    }
    public function destroy($slug)
    {
        $data = DestroyData::execute($slug);
        return $data;
    }
    public function restore()
    {
        $data = RestoreData::execute();
        return $data;
    }
    public function import()
    {
        $data = ImportData::execute();
        return $data;
    }
    public function bulkAction(BulkActionsValidation $request)
    {
        $data = BulkActions::execute();
        return $data;
    }

    public function quiz_questions()
    {
        $data = QuizQuestions::execute();
        return $data;
    }

    public function get_public_quizzes(Request $request)
    {

        $data = GetPublicQuizzes::execute();
        return $data;
    }
    public function validate_quiz_code()
    {
        $data = ValidateQuizCode::execute();
        return $data;
    }

    public function register_for_quiz(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'organization' => 'nullable|string|max:255'
        ]);

        $data = RegisterForQuiz::execute($request);
        return $data;
    }

    public function start_quiz_exam(Request $request)
    {
        $sessionToken = $request->header('Authorization') ? str_replace('Bearer ', '', $request->header('Authorization')) : null;

        if (!$sessionToken) {
            return messageResponse('Session token required', [], 401, 'unauthorized');
        }

        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id'
        ]);

        return StartQuizExam::execute($request, $sessionToken);
    }

    public function submit_quiz(Request $request)
    {
        $sessionToken = $request->header('Authorization') ? str_replace('Bearer ', '', $request->header('Authorization')) : null;

        // Also check X-Session-Token header as fallback
        if (!$sessionToken) {
            $sessionToken = $request->header('X-Session-Token');
        }

        if (!$sessionToken) {
            return messageResponse('Session token required', [], 401, 'unauthorized');
        }

        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'answers' => 'nullable|array',
            'duration' => 'required|integer|min:0',
            'submit_reason' => 'sometimes|string|max:255'
        ]);

        try {
            // Generate unique submission ID for tracking
            $submissionId = uniqid('quiz_submit_', true);

            // Quick validation before queuing - be more flexible with status
            $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;
            $participation = $participationModel::query()
                ->where('quiz_id', $request->quiz_id)
                ->where('session_token', $sessionToken)
                ->whereIn('status', ['active', 'processing']) // Allow both active and processing
                ->first();

            // Debug logging to help identify the issue
            if (!$participation) {
                \Illuminate\Support\Facades\Log::warning('Participation not found', [
                    'quiz_id' => $request->quiz_id,
                    'session_token' => substr($sessionToken, 0, 20) . '...',
                    'token_length' => strlen($sessionToken)
                ]);

                // Check if participation exists with any status
                $anyParticipation = $participationModel::query()
                    ->where('quiz_id', $request->quiz_id)
                    ->where('session_token', $sessionToken)
                    ->first();

                if ($anyParticipation) {
                    \Illuminate\Support\Facades\Log::info('Found participation with different status', [
                        'current_status' => $anyParticipation->status,
                        'is_completed' => $anyParticipation->is_completed
                    ]);

                    if ($anyParticipation->is_completed) {
                        return messageResponse('Quiz already submitted', [], 400, 'already_submitted');
                    }

                    // If status is not active/processing, reset it
                    if (!in_array($anyParticipation->status, ['active'])) {
                        $anyParticipation->update(['status' => 'active']);
                        $participation = $anyParticipation;
                    }
                } else {
                    return messageResponse('Invalid session', [], 401, 'invalid_session');
                }
            }

            if ($participation->is_completed) {
                return messageResponse('Quiz already submitted', [], 400, 'already_submitted');
            }

            // Mark as processing to prevent duplicate submissions
            $participation->update(['status' => 'active']);

            // Generate unique submission ID for tracking
            $submissionId = uniqid('quiz_submit_', true);

            // Extract only primitive values to avoid serialization issues
            $quizId = (int) $request->quiz_id;
            $answers = $request->answers ?? [];
            $duration = (int) $request->duration;
            $submitReason = $request->submit_reason ?? 'Normal completion';
            $sessionTokenValue = $sessionToken; // Create local copy

            // Prepare only the necessary data for the job (avoid serialization issues)
            $jobData = [
                'quiz_id' => $quizId,
                'answers' => $answers,
                'duration' => $duration,
                'submit_reason' => $submitReason
            ];

            // Create batch job with tracking
            $batch = \Illuminate\Support\Facades\Bus::batch([
                new SubmitQuizJob($jobData, $sessionTokenValue)
            ])
                ->name("Quiz Submission - Quiz ID: {$quizId}")
                ->allowFailures(false)
                ->onQueue('quiz_submissions')
                ->onConnection('database')
                ->then(function ($batch) use ($submissionId) {
                    \Illuminate\Support\Facades\Log::info('Quiz submission batch completed', [
                        'batch_id' => $batch->id,
                        'submission_id' => $submissionId
                    ]);
                })
                ->catch(function ($batch, $throwable) use ($quizId, $sessionTokenValue) {
                    \Illuminate\Support\Facades\Log::error('Quiz submission batch failed', [
                        'batch_id' => $batch->id,
                        'quiz_id' => $quizId,
                        'error' => $throwable->getMessage()
                    ]);

                    // Reset participation status for retry
                    $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;
                    $participationModel::query()
                        ->where('quiz_id', $quizId)
                        ->where('session_token', $sessionTokenValue)
                        ->update(['status' => 'active']);
                })
                ->dispatch();

            // Immediately run your worker in background
            exec('nohup php ' . base_path('artisan') . ' queue:process-once > /dev/null 2>&1 &');

            Artisan::call('queue:process-once');

            // Store batch info for tracking
            \Illuminate\Support\Facades\Cache::put("submission_batch_{$submissionId}", [
                'batch_id' => $batch->id,
                'quiz_id' => $quizId,
                'session_token' => $sessionTokenValue,
                'created_at' => now()->toISOString()
            ], 3600); // Keep for 1 hour

            // Return immediate response with tracking info
            return entityResponse([
                'message' => 'Quiz submission queued for processing',
                'submission_id' => $submissionId,
                'batch_id' => $batch->id,
                'status' => 'processing',
                'estimated_processing_time' => '5-10 seconds'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Quiz submission queue error', [
                'error' => $e->getMessage(),
                'quiz_id' => $request->quiz_id,
                'session_token' => substr($sessionToken, 0, 10) . '...'
            ]);

            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }

    // CHECK SUBMISSION STATUS
    public function check_submission_status(Request $request, $submissionId)
    {
        try {
            $batchInfo = \Illuminate\Support\Facades\Cache::get("submission_batch_{$submissionId}");

            if (!$batchInfo) {
                return messageResponse('Submission not found', [], 404, 'not_found');
            }

            $batch = \Illuminate\Support\Facades\Bus::findBatch($batchInfo['batch_id']);

            if (!$batch) {
                return messageResponse('Batch not found', [], 404, 'batch_not_found');
            }

            $status = 'processing';
            $message = 'Quiz submission is being processed';

            if ($batch->finished()) {
                $status = 'completed';
                $message = 'Quiz submitted successfully';

                // Get final results
                $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;
                $participation = $participationModel::query()
                    ->where('quiz_id', $batchInfo['quiz_id'])
                    ->where('session_token', $batchInfo['session_token'])
                    ->where('is_completed', true)
                    ->first();

                if ($participation) {
                    return entityResponse([
                        'status' => $status,
                        'message' => $message,
                        'results' => [
                            'obtained_marks' => $participation->obtained_marks,
                            'percentage' => $participation->percentage,
                            'is_passed' => $participation->is_passed,
                            'duration' => $participation->duration,
                            'submitted_at' => $participation->submitted_at
                        ]
                    ]);
                }
            } elseif ($batch->cancelled()) {
                $status = 'cancelled';
                $message = 'Quiz submission was cancelled';
            } elseif ($batch->hasFailures()) {
                $status = 'failed';
                $message = 'Quiz submission failed';
            }

            return entityResponse([
                'status' => $status,
                'message' => $message,
                'progress' => [
                    'total_jobs' => $batch->totalJobs,
                    'pending_jobs' => $batch->pendingJobs,
                    'processed_jobs' => $batch->processedJobs(),
                    'failed_jobs' => $batch->failedJobs
                ]
            ]);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }

    // DEBUG ENDPOINT - Remove after testing
    public function debug_session(Request $request)
    {
        $sessionToken = $request->header('Authorization') ? str_replace('Bearer ', '', $request->header('Authorization')) : null;

        // Also check X-Session-Token header as fallback
        if (!$sessionToken) {
            $sessionToken = $request->header('X-Session-Token');
        }

        $quizId = $request->input('quiz_id');

        $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;

        // Find all participations for this quiz
        $allParticipations = $participationModel::query()
            ->where('quiz_id', $quizId)
            ->get(['id', 'user_id', 'quiz_id', 'session_token', 'status', 'is_completed', 'created_at'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'user_id' => $p->user_id,
                    'quiz_id' => $p->quiz_id,
                    'session_token_start' => substr($p->session_token, 0, 20) . '...',
                    'session_token_length' => strlen($p->session_token),
                    'status' => $p->status,
                    'is_completed' => $p->is_completed,
                    'created_at' => $p->created_at
                ];
            });

        // Find exact match
        $exactMatch = $participationModel::query()
            ->where('quiz_id', $quizId)
            ->where('session_token', $sessionToken)
            ->first();

        return [
            'provided_session_token' => [
                'length' => strlen($sessionToken),
                'start' => substr($sessionToken, 0, 20) . '...'
            ],
            'quiz_id' => $quizId,
            'all_participations' => $allParticipations,
            'exact_match' => $exactMatch ? [
                'id' => $exactMatch->id,
                'status' => $exactMatch->status,
                'is_completed' => $exactMatch->is_completed
            ] : null
        ];
    }

    public function export_results(Request $request, $quizId)
    {
        return ExportQuizResults::execute($request, $quizId);
    }

    public function get_participant_result(Request $request)
    {
        $sessionToken = $request->header('Authorization') ? str_replace('Bearer ', '', $request->header('Authorization')) : null;

        if (!$sessionToken) {
            return messageResponse('Session token required', [], 401, 'unauthorized');
        }

        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id'
        ]);

        return GetParticipantResult::execute($request, $sessionToken);
    }
}
