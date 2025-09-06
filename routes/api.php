<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\StaffController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Create ticket (rate-limited)
    Route::post('/tickets', [TicketController::class, 'store'])->middleware('throttle:10,1');

    // List tickets (rate-limited)
    Route::get('/tickets', [TicketController::class, 'index'])->middleware('throttle:60,1');

    // Update ticket status (numeric id, rate-limited)
    Route::patch('/tickets/{id}', [TicketController::class, 'updateStatus'])->whereNumber('id')->middleware('throttle:30,1');

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/staff', [StaffController::class, 'index']);
});