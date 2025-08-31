<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Modules\Controllers\Frontend\FrontendController;
use App\Modules\Controllers\Frontend\Auth\AuthController as BackendAuthController;


Route::get('/', [FrontendController::class, 'HomePage'])->name('HomePage');
Route::get('/quiz', [FrontendController::class, 'QuizPage'])->name('QuizPage');
Route::get('/result', [FrontendController::class, 'Result'])->name('Result');
Route::get('/registers', [FrontendController::class, 'Register'])->name('Register');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin-login', [BackendAuthController::class, 'LoginPage'])->name('LoginPage');
// Route::get('/register', [AuthController::class, 'RegisterPage'])->name('RegisterPage');
// Route::get('/forgot-password', [AuthController::class, 'ForgotPassword'])->name('ForgotPassword');

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login_submit'])->name('login_sumbit');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register-sumbit', [AuthController::class, 'register_sumbit'])->name('register_sumbit');

Route::post('logout', [AuthController::class, 'logout_submit'])->name('logout');


