<?php

use App\Http\Controllers\Ticket\TicketController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);

    Route::post('/tickets', [TicketController::class, 'store'])
        ->middleware('permission:create-ticket');

    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
});
