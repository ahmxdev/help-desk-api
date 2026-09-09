<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication core
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::get('/me', [AuthController::class, 'me'])
    ->middleware('auth:sanctum');


// Email verification
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');

Route::post('email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
    ->middleware('auth:sanctum', 'throttle:verification-notification');


// Forgot and change password
Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:forgot-password');

Route::post('reset-password', [AuthController::class, 'resetPassword'])
    ->name('password.update');

Route::post('change-password', [AuthController::class, 'changePassword'])
    ->middleware('auth:sanctum')
    ->name('password.change');
