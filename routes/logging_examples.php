<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Examples\UserActivityExampleController;

/*
|--------------------------------------------------------------------------
| User Activity Logging Examples
|--------------------------------------------------------------------------
| These routes demonstrate all three logging methods:
| 1. Middleware (Automatic)
| 2. Trait Methods (Easy & Clean)  
| 3. Manual Events (Full Control)
*/

Route::middleware(['auth', 'log.activity'])->prefix('logging-examples')->group(function () {
    
    // Example 1: Automatic logging via middleware
    Route::get('/automatic', [UserActivityExampleController::class, 'automaticLogging'])
        ->name('logging.automatic');
    
    // Example 2: Trait-based logging
    Route::post('/trait', [UserActivityExampleController::class, 'traitLogging'])
        ->name('logging.trait');
    
    // Example 3: Manual event logging
    Route::put('/manual', [UserActivityExampleController::class, 'manualLogging'])
        ->name('logging.manual');
    
    // Example 4: Combined approach
    Route::patch('/combined', [UserActivityExampleController::class, 'combinedLogging'])
        ->name('logging.combined');
    
    // Example 5: Test specific route (no middleware)
    Route::get('/no-middleware', [UserActivityExampleController::class, 'manualLogging'])
        ->withoutMiddleware(['log.activity'])
        ->name('logging.no-middleware');
    
});

// Routes with different middleware combinations
Route::middleware(['auth'])->group(function () {
    // Only auth, no activity logging middleware
    Route::get('/auth-only', [UserActivityExampleController::class, 'automaticLogging'])
        ->name('logging.auth-only');
});

// Public route (no auth, no logging)
Route::get('/public-example', function() {
    return response()->json([
        'message' => 'Public route - no automatic logging',
        'note' => 'Manual logging still possible if user is authenticated'
    ]);
})->name('logging.public');
