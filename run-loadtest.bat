@echo off
setlocal enabledelayedexpansion

echo === Quiz Load Testing Setup ===
echo.

REM Check if k6 is installed
where k6 >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ k6 is not installed. Please install k6 first:
    echo    - Windows: choco install k6 or download from https://k6.io/docs/get-started/installation/
    echo    - Or download from: https://github.com/grafana/k6/releases
    exit /b 1
)

echo ✅ k6 is installed
echo.

REM Function to check if Laravel server is running
curl -s "http://127.0.0.1:8000/api/v1/public-quizzes" >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ Laravel server is not running
    echo    Please start your Laravel server with: php artisan serve
    exit /b 1
)

echo ✅ Laravel server is running
echo.

echo 🔄 Checking queue workers...
echo    Make sure you have queue workers running:
echo    php artisan queue:work --verbose --tries=3 --timeout=90
echo.
set /p workers_running="Are your queue workers running? (y/n): "
if /i not "%workers_running%"=="y" (
    echo ❌ Please start queue workers before running the load test
    exit /b 1
)
echo ✅ Queue workers confirmed
echo.

echo 📊 Preparing database users for load testing...
php export-users.php
if %errorlevel% neq 0 (
    echo ❌ Failed to export users. Creating additional users...
    php create-loadtest-users.php
    php export-users.php
)
echo ✅ Users ready for load testing
echo.

echo Choose a load test to run:
echo 1. Simple Test (100 users, 2 minutes) - Good for initial testing
echo 2. Full Load Test (up to 20k users, 35 minutes) - Production stress test
echo 3. Custom Test (enter your own parameters)
echo 4. Status Check (monitor existing load test)
echo.
set /p choice="Enter your choice (1-4): "

if "%choice%"=="1" (
    echo 🚀 Starting Simple Load Test...
    echo.
    echo 🧹 Clearing old jobs...
    php artisan queue:clear
    php artisan queue:restart
    echo ✅ Old jobs cleared
    echo.
    echo Running test with 100 virtual users for 2 minutes...
    k6 run loadtest-simple.js
) else if "%choice%"=="2" (
    echo 🚀 Starting Full Load Test...
    echo.
    echo ⚠️  WARNING: This will simulate up to 20,000 concurrent users!
    echo    Make sure your system can handle this load.
    set /p confirm="Continue? (y/n): "
    if /i "!confirm!"=="y" (
        echo 🧹 Clearing old jobs...
        php artisan queue:clear
        php artisan queue:restart
        echo ✅ Old jobs cleared
        echo.
        echo Running full load test (this will take ~35 minutes)...
        k6 run loadtest.js
    ) else (
        echo Load test cancelled.
    )
) else if "%choice%"=="3" (
    echo 🛠️  Custom Load Test...
    set /p vus="Enter number of virtual users: "
    set /p duration="Enter test duration (e.g., 5m, 30s): "
    
    REM Create temporary test file
    (
        echo import http from 'k6/http';
        echo import { sleep, check } from 'k6';
        echo.
        echo export let options = {
        echo   vus: !vus!,
        echo   duration: '!duration!',
        echo };
        echo.
        echo export default function () {
        echo   const baseUrl = 'http://127.0.0.1:8000/api/v1';
        echo   const user = {
        echo     name: `User${__VU}`,
        echo     email: `user${__VU}@test.com`,
        echo     phone: `01${String(Math.floor(Math.random() * 900000000) + 100000000))}`,
        echo     organization: `Org${Math.floor(Math.random() * 10) + 1}`,
        echo   };
        echo.  
        echo   const headers = {
        echo     'Content-Type': 'application/json',
        echo     'X-Forwarded-For': `192.168.1.${(__VU %% 254) + 1}`,
        echo   };
        echo.
        echo   try {
        echo     const registrationResponse = http.post(
        echo       `${baseUrl}/public-quizzes/register`,
        echo       JSON.stringify({
        echo         quiz_id: 2,
        echo         name: user.name,
        echo         email: user.email,
        echo         phone: user.phone,
        echo         organization: user.organization
        echo       }),
        echo       { headers }
        echo     );
        echo.
        echo     if (!check(registrationResponse, { 'registration ok': (r) => r.status === 200 ^|^| r.status === 201 })) {
        echo       return;
        echo     }
        echo.
        echo     const regData = JSON.parse(registrationResponse.body);
        echo     const sessionToken = regData.data.session_token;
        echo     sleep(0.5);
        echo.
        echo     const submissionResponse = http.post(
        echo       `${baseUrl}/quizzes/submit_exam`,
        echo       JSON.stringify({
        echo         quiz_id: 2,
        echo         answers: { 15: [57, 58], 16: [61, 62] },
        echo         duration: Math.floor(Math.random() * 300) + 60,
        echo         submit_reason: "Normal completion",
        echo         security_data: { tabSwitchCount: 0, violations: [] }
        echo       }),
        echo       { headers: { ...headers, 'Authorization': `Bearer ${sessionToken}` } }
        echo     );
        echo.
        echo     check(submissionResponse, { 'submission ok': (r) => r.status === 200 ^|^| r.status === 202 });
        echo   } catch (error) {
        echo     console.log(`Error: ${error.message}`);
        echo   }
        echo   sleep(0.5);
        echo }
    ) > temp-loadtest.js
    
    echo 🧹 Clearing old jobs...
    php artisan queue:clear
    php artisan queue:restart
    echo ✅ Old jobs cleared
    echo.
    echo Running custom test with !vus! users for !duration!...
    k6 run temp-loadtest.js
    del temp-loadtest.js
) else if "%choice%"=="4" (
    echo 📊 Status Check...
    echo === Queue Status ===
    php artisan queue:status
    echo.
    echo === Failed Jobs ===
    php artisan queue:failed
    echo.
    echo === Recent Batch Jobs ===
    php artisan tinker --execute="DB::table('job_batches')->orderBy('created_at', 'desc')->limit(5)->get()->each(function($batch) { echo \"Batch {$batch->id}: {$batch->name} - {$batch->pending_jobs} pending, {$batch->failed_jobs} failed\" . PHP_EOL; });"
) else (
    echo ❌ Invalid choice. Please run the script again.
    exit /b 1
)

echo.
echo === Load Test Complete ===
echo.
echo 📋 Next Steps:
echo 1. Check your Laravel logs: Get-Content storage\logs\laravel.log -Tail 50 -Wait
echo 2. Monitor queue status: php artisan queue:status
echo 3. Check batch job status: php artisan tinker
echo 4. Review failed jobs: php artisan queue:failed
echo 5. Monitor system resources (CPU, Memory, Database connections)
echo.
echo 🔧 Useful Commands:
echo    - Restart queue workers: php artisan queue:restart
echo    - Retry failed jobs: php artisan queue:retry all
echo    - Clear failed jobs: php artisan queue:flush
echo    - Check submission status: curl http://127.0.0.1:8000/api/v1/quizzes/submission_status/[ID]

pause
