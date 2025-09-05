<?php

namespace App\Modules\Management\QuizManagement\Quiz\Others;

use App\Modules\Management\QuizManagement\Quiz\Actions\SubmitQuizExam;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubmitQuizJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData;
    protected $sessionToken;
    
    // Job configuration
    public $timeout = 120;
    public $tries = 3;
    public $backoff = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(array $requestData, string $sessionToken)
    {
        // Only store serializable data to avoid PDO serialization issues
        $this->requestData = [
            'quiz_id' => (int) ($requestData['quiz_id'] ?? 0),
            'answers' => $requestData['answers'] ?? [],
            'duration' => (int) ($requestData['duration'] ?? 0),
            'submit_reason' => (string) ($requestData['submit_reason'] ?? 'Normal completion')
        ];
        
        $this->sessionToken = $sessionToken;
        $this->onQueue('quiz_submissions');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if batch is cancelled
            if ($this->batch()->cancelled()) {
                return;
            }

            // Create a clean request object with only the necessary data
            $request = new Request();
            $request->merge([
                'quiz_id' => $this->requestData['quiz_id'],
                'answers' => $this->requestData['answers'] ?? [],
                'duration' => $this->requestData['duration'],
                'submit_reason' => $this->requestData['submit_reason'] ?? 'Normal completion'
            ]);

            // Execute the original quiz submission logic
            $result = SubmitQuizExam::execute($request, $this->sessionToken);

            // Handle Response object - extract the status code and content
            if ($result instanceof \Illuminate\Http\Response || $result instanceof \Illuminate\Http\JsonResponse) {
                $statusCode = $result->getStatusCode();
                $content = $result->getContent();
                $resultData = json_decode($content, true);
                
                // Log successful processing
                if ($statusCode == 200) {
                    Log::info('Quiz submitted successfully via job', [
                        'quiz_id' => $this->requestData['quiz_id'] ?? 'unknown',
                        'session_token' => substr($this->sessionToken, 0, 10) . '...',
                        'batch_id' => $this->batch()->id
                    ]);
                } else {
                    // If submission failed, fail the job
                    $errorMessage = $resultData['message'] ?? 'Unknown error during quiz submission';
                    Log::error('Quiz submission failed in job', [
                        'error' => $errorMessage,
                        'status_code' => $statusCode,
                        'quiz_id' => $this->requestData['quiz_id'] ?? 'unknown',
                        'session_token' => substr($this->sessionToken, 0, 10) . '...',
                        'batch_id' => $this->batch()->id
                    ]);
                    $this->fail($errorMessage);
                }
            } else {
                // Fallback for unexpected response format
                Log::warning('Unexpected response format from SubmitQuizExam', [
                    'response_type' => gettype($result),
                    'quiz_id' => $this->requestData['quiz_id'] ?? 'unknown'
                ]);
                $this->fail('Unexpected response format from quiz submission');
            }

        } catch (\Exception $e) {
            Log::error('Quiz submission job failed', [
                'error' => $e->getMessage(),
                'quiz_id' => $this->requestData['quiz_id'] ?? 'unknown',
                'session_token' => substr($this->sessionToken, 0, 10) . '...',
                'batch_id' => $this->batch()?->id
            ]);
            
            // Fail the job to trigger retry mechanism
            $this->fail($e);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Quiz submission job permanently failed', [
            'error' => $exception->getMessage(),
            'quiz_id' => $this->requestData['quiz_id'] ?? 'unknown',
            'session_token' => substr($this->sessionToken, 0, 10) . '...',
            'batch_id' => $this->batch()?->id,
            'attempts' => $this->attempts()
        ]);

        // Reset participation status so user can try again
        try {
            $participationModel = \App\Modules\Management\QuizManagement\QuizParticipation\Models\Model::class;
            $participationModel::query()
                ->where('quiz_id', $this->requestData['quiz_id'])
                ->where('session_token', $this->sessionToken)
                ->where('status', 'processing')
                ->update(['status' => 'active']);
        } catch (\Exception $e) {
            Log::error('Failed to reset participation status', [
                'error' => $e->getMessage(),
                'quiz_id' => $this->requestData['quiz_id'] ?? 'unknown'
            ]);
        }
    }
}
