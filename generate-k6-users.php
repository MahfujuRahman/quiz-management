<?php

// Generate k6-compatible user data file from database
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Modules\Management\QuizManagement\QuizParticipation\Models\Model as Participation;

try {
    // Get active participations
    $participations = Participation::where('is_completed', false)
        ->where('status', 'active')
        ->limit(200)
        ->get(['id', 'quiz_id', 'session_token', 'name', 'email']);

    // Convert to JavaScript array format
    $jsUsers = [];
    foreach ($participations as $participation) {
        $jsUsers[] = [
            'id' => $participation->id,
            'quiz_id' => $participation->quiz_id,
            'session_token' => $participation->session_token,
            'name' => $participation->name,
            'email' => $participation->email
        ];
    }

    // Generate JavaScript file
    $jsContent = "// Auto-generated user data for k6 load testing\n";
    $jsContent .= "// Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    $jsContent .= "export const exportedUsers = " . json_encode($jsUsers, JSON_PRETTY_PRINT) . ";\n";

    file_put_contents('users-data.js', $jsContent);

    echo "✅ Generated users-data.js with " . count($jsUsers) . " users\n";
    echo "This file can be imported in k6 scripts.\n";

    // Also update the simple load test to use this data
    echo "\n📝 Updating loadtest-simple.js to use exported data...\n";

    $loadTestContent = file_get_contents('loadtest-simple.js');
    
    // Replace the userData SharedArray section
    $newUserDataSection = <<<'JS'
// Load real users from database export (k6 compatible)
const userData = new SharedArray('users', function () {
  // Import the exported users (uncomment the line below and comment the fallback)
  // import { exportedUsers } from './users-data.js';
  // return exportedUsers;
  
  // Fallback: Use actual session tokens from database
  const realUsers = JS . json_encode($jsUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ';
  
  console.log(`Loaded ${realUsers.length} real users from database`);
  return realUsers;
});
JS;

    // Find and replace the userData section
    $pattern = '/\/\/ Load real users from database export \(k6 compatible\).*?}\);/s';
    $updatedContent = preg_replace($pattern, $newUserDataSection, $loadTestContent);
    
    if ($updatedContent && $updatedContent !== $loadTestContent) {
        file_put_contents('loadtest-simple.js', $updatedContent);
        echo "✅ Updated loadtest-simple.js with real database users\n";
    }

    echo "\nSample users:\n";
    foreach (array_slice($jsUsers, 0, 3) as $user) {
        echo "- {$user['name']} ({$user['email']}) - Quiz {$user['quiz_id']} - Token: " . substr($user['session_token'], 0, 20) . "...\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
