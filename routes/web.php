<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('tickets.create');
});

use App\Http\Controllers\StaffController;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

use App\Http\Controllers\TicketController;

Route::get('/tickets/{recepient_id?}', [TicketController::class, 'index'])->name('tickets.index');
Route::get('/tickets/create/{recepient_id?}', [TicketController::class, 'showCreateForm'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');

Route::middleware('auth')->group(function () {
    // Staff dashboard
    Route::get('/staff/dashboard', [StaffController::class, 'index'])->name('staff.dashboard');
    
    // Admin dashboard (placeholder for now)
    Route::get('/admin/dashboard', function () {
        return view('dashboards.admin.index'); // Placeholder for admin dashboard
    })->name('admin.dashboard');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
