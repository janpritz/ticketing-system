<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\StaffController;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tickets', [TicketController::class, 'create']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::patch('/tickets/{id}', [TicketController::class, 'updateStatus']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/staff', [StaffController::class, 'index']);
});