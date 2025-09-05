<?php

// Export active quiz participants for load testing
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Modules\Management\QuizManagement\QuizParticipation\Models\Model as Participation;

try {
    // Get active participations that haven't been completed
    $participations = Participation::where('is_completed', false)
        ->where('status', 'active')
        ->limit(200)
        ->get(['id', 'quiz_id', 'session_token', 'name', 'email']);

    // Convert to array format suitable for k6
    $users = $participations->map(function ($participation) {
        return [
            'id' => $participation->id,
            'quiz_id' => $participation->quiz_id,
            'session_token' => $participation->session_token,
            'name' => $participation->name,
            'email' => $participation->email
        ];
    })->toArray();

    // Save to JSON file
    $jsonData = json_encode($users, JSON_PRETTY_PRINT);
    file_put_contents('users-for-loadtest.json', $jsonData);

    echo "✅ Exported " . count($users) . " users to users-for-loadtest.json\n";
    echo "Users are ready for load testing!\n";
    
    // Show sample of exported data
    echo "\nSample users:\n";
    foreach (array_slice($users, 0, 3) as $user) {
        echo "- {$user['name']} ({$user['email']}) - Quiz {$user['quiz_id']} - Token: " . substr($user['session_token'], 0, 20) . "...\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
