<?php

use App\Http\Controllers\Message\MessageController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/messages', [MessageController::class, 'send']);
});
