<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TicketController;

Route::get('/', function () {
    // If the user is authenticated, auto-redirect them to the appropriate dashboard
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'Primary Administrator') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('staff.dashboard');
    }

    // Guests still see the public ticket create page
    return view('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest','throttle:10,1');

Route::get('/tickets/{recepient_id?}', [TicketController::class, 'index'])->whereNumber('recepient_id')->name('tickets.index');
Route::get('/tickets/create/{recepient_id?}', [TicketController::class, 'showCreateForm'])->whereNumber('recepient_id')->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->middleware('throttle:10,1')->name('tickets.store');

Route::middleware('auth')->group(function () {
    // Staff dashboard
    Route::get('/staff/dashboard', [StaffController::class, 'index'])->name('staff.dashboard');
    // Live data endpoint for staff dashboard auto-refresh
    Route::get('/staff/dashboard/data', [StaffController::class, 'data'])->middleware('throttle:20,1')->name('staff.dashboard.data');

    // Ticket reroute (records history)
    Route::post('/staff/tickets/{ticket}/reroute', [StaffController::class, 'reroute'])
        ->whereNumber('ticket')
        ->middleware('throttle:30,1')
        ->name('staff.tickets.reroute');
 
    // Ticket respond (send email)
    Route::post('/staff/tickets/{ticket}/respond', [StaffController::class, 'respond'])
        ->whereNumber('ticket')
        ->middleware('throttle:20,1')
        ->name('staff.tickets.respond');

    // SMTP test endpoint (sends to the authenticated user's email)
    Route::get('/staff/mail/test', [StaffController::class, 'mailTest'])->middleware('throttle:5,1')->name('staff.mail.test');
    
    // Admin dashboard (placeholder for now)
    Route::get('/admin/dashboard', function () {
        return view('dashboards.admin.index'); // Placeholder for admin dashboard
    })->name('admin.dashboard');

    // Logout (authenticated only)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

