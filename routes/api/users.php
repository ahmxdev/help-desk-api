<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;


Route::patch('/users/{user}/role', [UserController::class, 'setRole'])
    ->middleware('auth:sanctum', 'role:admin');
