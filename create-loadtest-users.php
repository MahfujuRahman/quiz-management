<?php

// Create additional quiz participants for load testing if needed
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Modules\Management\QuizManagement\QuizParticipation\Models\Model as Participation;
use Illuminate\Support\Str;

$targetUsers = 500; // Target number of users for load testing
$quizId = 2; // Use quiz ID 2

try {
    // Check how many active users we already have
    $existingCount = Participation::where('is_completed', false)
        ->where('status', 'active')
        ->count();

    echo "Current active users: {$existingCount}\n";

    if ($existingCount >= $targetUsers) {
        echo "✅ Already have enough users for load testing!\n";
        exit(0);
    }

    $needed = $targetUsers - $existingCount;
    echo "Creating {$needed} additional users...\n";

    $created = 0;
    for ($i = 1; $i <= $needed; $i++) {
        $sessionToken = Str::random(64);
        
        try {
            Participation::create([
                'quiz_id' => $quizId,
                'session_token' => $sessionToken,
                'name' => "LoadTestUser{$i}",
                'email' => "loadtest{$i}@example.com",
                'phone' => "01" . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'organization' => "LoadTest Organization " . rand(1, 10),
                'started_at' => now(),
                'is_completed' => false,
                'status' => 'active'
            ]);
            $created++;
            
            if ($created % 50 == 0) {
                echo "Created {$created} users...\n";
            }
        } catch (Exception $e) {
            echo "Failed to create user {$i}: " . $e->getMessage() . "\n";
        }
    }

    echo "✅ Created {$created} new users for load testing!\n";
    echo "Total active users now: " . Participation::where('is_completed', false)->where('status', 'active')->count() . "\n";
    echo "\nRun 'php export-users.php' to export the updated user list.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
