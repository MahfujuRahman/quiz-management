#!/usr/bin/env bash

# Load Testing Script for Quiz System
# Make sure to start your Laravel queue workers before running this script

echo "=== Quiz Load Testing Setup ==="
echo ""

# Check if k6 is installed
if ! command -v k6 &> /dev/null; then
    echo "❌ k6 is not installed. Please install k6 first:"
    echo "   - Windows: choco install k6 or download from https://k6.io/docs/get-started/installation/"
    echo "   - macOS: brew install k6"
    echo "   - Linux: sudo apt-get install k6"
    exit 1
fi

echo "✅ k6 is installed"
echo ""

# Function to check if Laravel server is running
check_server() {
    if curl -s "http://127.0.0.1:8000/api/v1/public-quizzes" > /dev/null; then
        echo "✅ Laravel server is running"
        return 0
    else
        echo "❌ Laravel server is not running"
        echo "   Please start your Laravel server with: php artisan serve"
        return 1
    fi
}

# Function to check queue workers
check_queue_workers() {
    echo "🔄 Checking queue workers..."
    echo "   Make sure you have queue workers running:"
    echo "   php artisan queue:work --verbose --tries=3 --timeout=90"
    echo ""
    read -p "Are your queue workers running? (y/n): " workers_running
    if [[ $workers_running != "y" && $workers_running != "Y" ]]; then
        echo "❌ Please start queue workers before running the load test"
        exit 1
    fi
    echo "✅ Queue workers confirmed"
}

# Function to clear old jobs
clear_old_jobs() {
    echo "🧹 Clearing old failed jobs and resetting batch jobs..."
    php artisan queue:clear
    php artisan queue:restart
    # Clear batch jobs table if needed
    php artisan tinker --execute="DB::table('job_batches')->truncate(); echo 'Batch jobs cleared';"
    echo "✅ Old jobs cleared"
}

# Main menu
echo "Choose a load test to run:"
echo "1. Simple Test (100 users, 2 minutes) - Good for initial testing"
echo "2. Full Load Test (up to 20k users, 35 minutes) - Production stress test"
echo "3. Custom Test (enter your own parameters)"
echo "4. Status Check (monitor existing load test)"
echo ""
read -p "Enter your choice (1-4): " choice

case $choice in
    1)
        echo "🚀 Starting Simple Load Test..."
        check_server || exit 1
        check_queue_workers
        clear_old_jobs
        echo ""
        echo "Running test with 100 virtual users for 2 minutes..."
        k6 run loadtest-simple.js
        ;;
    2)
        echo "🚀 Starting Full Load Test..."
        check_server || exit 1
        check_queue_workers
        clear_old_jobs
        echo ""
        echo "⚠️  WARNING: This will simulate up to 20,000 concurrent users!"
        echo "   Make sure your system can handle this load."
        read -p "Continue? (y/n): " confirm
        if [[ $confirm == "y" || $confirm == "Y" ]]; then
            echo "Running full load test (this will take ~35 minutes)..."
            k6 run loadtest.js
        else
            echo "Load test cancelled."
        fi
        ;;
    3)
        echo "🛠️  Custom Load Test..."
        check_server || exit 1
        check_queue_workers
        read -p "Enter number of virtual users: " vus
        read -p "Enter test duration (e.g., 5m, 30s): " duration
        
        # Create temporary test file
        cat > temp-loadtest.js << EOF
import http from 'k6/http';
import { sleep, check } from 'k6';

export let options = {
  vus: ${vus},
  duration: '${duration}',
};

export default function () {
  const baseUrl = 'http://127.0.0.1:8000/api/v1';
  const user = {
    name: \`User\${__VU}\`,
    email: \`user\${__VU}@test.com\`,
    phone: \`01\${String(Math.floor(Math.random() * 900000000) + 100000000)}\`,
    organization: \`Org\${Math.floor(Math.random() * 10) + 1}\`,
  };
  
  const headers = {
    'Content-Type': 'application/json',
    'X-Forwarded-For': \`192.168.1.\${(__VU % 254) + 1}\`,
  };

  try {
    // Register
    const registrationResponse = http.post(
      \`\${baseUrl}/public-quizzes/register\`,
      JSON.stringify({
        quiz_id: 2,
        name: user.name,
        email: user.email,
        phone: user.phone,
        organization: user.organization
      }),
      { headers }
    );

    if (!check(registrationResponse, { 'registration ok': (r) => r.status === 200 || r.status === 201 })) {
      return;
    }

    const regData = JSON.parse(registrationResponse.body);
    const sessionToken = regData.data.session_token;

    sleep(0.5);

    // Submit quiz
    const submissionResponse = http.post(
      \`\${baseUrl}/quizzes/submit_exam\`,
      JSON.stringify({
        quiz_id: 2,
        answers: { 15: [57, 58], 16: [61, 62] },
        duration: Math.floor(Math.random() * 300) + 60,
        submit_reason: "Normal completion",
        security_data: { tabSwitchCount: 0, violations: [] }
      }),
      { headers: { ...headers, 'Authorization': \`Bearer \${sessionToken}\` } }
    );

    check(submissionResponse, { 'submission ok': (r) => r.status === 200 || r.status === 202 });

  } catch (error) {
    console.log(\`Error: \${error.message}\`);
  }

  sleep(0.5);
}
EOF
        
        clear_old_jobs
        echo "Running custom test with ${vus} users for ${duration}..."
        k6 run temp-loadtest.js
        rm temp-loadtest.js
        ;;
    4)
        echo "📊 Status Check..."
        echo "=== Queue Status ==="
        php artisan queue:status
        echo ""
        echo "=== Failed Jobs ==="
        php artisan queue:failed
        echo ""
        echo "=== Batch Jobs Status ==="
        php artisan tinker --execute="
        \$batches = DB::table('job_batches')->orderBy('created_at', 'desc')->limit(10)->get();
        foreach(\$batches as \$batch) {
            echo \"Batch {\$batch->id}: {\$batch->name} - {\$batch->pending_jobs} pending, {\$batch->failed_jobs} failed\" . PHP_EOL;
        }
        "
        ;;
    *)
        echo "❌ Invalid choice. Please run the script again."
        exit 1
        ;;
esac

echo ""
echo "=== Load Test Complete ==="
echo ""
echo "📋 Next Steps:"
echo "1. Check your Laravel logs: tail -f storage/logs/laravel.log"
echo "2. Monitor queue status: php artisan queue:status"
echo "3. Check batch job status: php artisan tinker"
echo "4. Review failed jobs: php artisan queue:failed"
echo "5. Monitor system resources (CPU, Memory, Database connections)"
echo ""
echo "🔧 Useful Commands:"
echo "   - Restart queue workers: php artisan queue:restart"
echo "   - Retry failed jobs: php artisan queue:retry all"
echo "   - Clear failed jobs: php artisan queue:flush"
echo "   - Check submission status: curl http://127.0.0.1:8000/api/v1/quizzes/submission_status/[ID]"
