<?php

use App\Http\Controllers\Ticket\TicketController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store'])->middleware('permission:create-ticket');
});
