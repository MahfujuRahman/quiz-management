@echo off
setlocal enabledelayedexpansion

echo 🎯 Configurable Load Test Runner
echo.

if "%1"=="" (
    set /p TARGET_REQUESTS="Enter target number of requests (e.g., 1000, 5000, 10000): "
) else (
    set TARGET_REQUESTS=%1
)

if "%TARGET_REQUESTS%"=="" (
    echo ❌ No target specified. Using default 1000.
    set TARGET_REQUESTS=1000
)

echo 📊 Configuring load test for %TARGET_REQUESTS% requests...

REM Create a temporary load test file with the specified target
(
    echo const TARGET_REQUESTS = %TARGET_REQUESTS%;
    echo.
    type loadtest-configurable.js | findstr /v "const TARGET_REQUESTS"
) > temp-loadtest-%TARGET_REQUESTS%.js

echo ✅ Created temporary test file: temp-loadtest-%TARGET_REQUESTS%.js

REM Calculate estimated time
set /a RATE_PER_IP=54
set /a MIN_IPS=!TARGET_REQUESTS!/!RATE_PER_IP!+1
set /a DURATION=!TARGET_REQUESTS!/(!RATE_PER_IP!*!MIN_IPS!)+2

echo 📈 Test Configuration:
echo    Target Requests: %TARGET_REQUESTS%
echo    Estimated IPs needed: !MIN_IPS!
echo    Estimated duration: ~!DURATION! minutes
echo    Rate per IP: ~%RATE_PER_IP% req/min
echo.

set /p CONFIRM="Continue with load test? (y/n): "
if /i not "%CONFIRM%"=="y" (
    echo Test cancelled.
    del temp-loadtest-%TARGET_REQUESTS%.js 2>nul
    exit /b 0
)

echo 🚀 Starting load test with %TARGET_REQUESTS% requests...
echo ⏰ Started at: %time%
echo.

k6 run temp-loadtest-%TARGET_REQUESTS%.js

echo.
echo ✅ Load test completed at: %time%
echo 🧹 Cleaning up temporary files...
del temp-loadtest-%TARGET_REQUESTS%.js 2>nul

echo.
echo 📋 Next Steps:
echo 1. Check Laravel logs: Get-Content storage\logs\laravel.log -Tail 50
echo 2. Monitor queue status: php artisan queue:status
echo 3. Check batch jobs: php artisan tinker
echo 4. Review system resources

pause
