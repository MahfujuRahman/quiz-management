<?php

// Reset quiz participations for load testing
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Modules\Management\QuizManagement\QuizParticipation\Models\Model as Participation;
use Illuminate\Support\Str;

$option = $argv[1] ?? 'reset';
$targetUsers = 1000; // Target number of users
$quizId = 2; // Use quiz ID 2

try {
    if ($option === 'reset') {
        echo "🔄 Resetting existing quiz participations...\n";
        
        // Reset completed participations back to active/incomplete
        $resetCount = Participation::where('is_completed', true)
            ->where('quiz_id', $quizId)
            ->update([
                'is_completed' => false,
                'status' => 'active',
                'answers' => null,
                'obtained_marks' => 0,
                'percentage' => 0,
                'duration' => null,
                'submit_reason' => 'Normal completion', // Can't be null
                'submitted_at' => null,
                'is_passed' => false
            ]);
            
        echo "✅ Reset {$resetCount} existing participations\n";
        
    } elseif ($option === 'create') {
        echo "➕ Creating new quiz participations...\n";
        
        // Check existing count
        $existingCount = Participation::where('is_completed', false)
            ->where('status', 'active')
            ->where('quiz_id', $quizId)
            ->count();
            
        echo "Current active users: {$existingCount}\n";
        
        if ($existingCount >= $targetUsers) {
            echo "✅ Already have enough users for testing!\n";
            exit(0);
        }
        
        $needed = $targetUsers - $existingCount;
        echo "Creating {$needed} new users...\n";
        
        $created = 0;
        for ($i = 1; $i <= $needed; $i++) {
            $sessionToken = Str::random(64);
            
            try {
                Participation::create([
                    'quiz_id' => $quizId,
                    'session_token' => $sessionToken,
                    'name' => "LoadTestUser" . ($existingCount + $i),
                    'email' => "loadtest" . ($existingCount + $i) . "@example.com",
                    'phone' => "01" . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                    'organization' => "LoadTest Organization " . rand(1, 10),
                    'started_at' => now(),
                    'is_completed' => false,
                    'status' => 'active'
                ]);
                $created++;
                
                if ($created % 100 == 0) {
                    echo "Created {$created} users...\n";
                }
            } catch (Exception $e) {
                echo "Failed to create user {$i}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ Created {$created} new users\n";
    }
    
    // Show current stats
    $totalActive = Participation::where('is_completed', false)
        ->where('status', 'active')
        ->where('quiz_id', $quizId)
        ->count();
        
    $totalCompleted = Participation::where('is_completed', true)
        ->where('quiz_id', $quizId)
        ->count();
        
    echo "\n📊 Current Status:\n";
    echo "   Active (can submit): {$totalActive}\n";
    echo "   Completed: {$totalCompleted}\n";
    echo "   Total: " . ($totalActive + $totalCompleted) . "\n";
    
    echo "\n🔧 Next steps:\n";
    echo "1. Run: php generate-k6-users.php (to export updated users)\n";
    echo "2. Run your load test with k6\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nUsage:\n";
echo "  php reset-quiz-participations.php reset   - Reset completed quizzes to active\n";
echo "  php reset-quiz-participations.php create  - Create new quiz participations\n";
