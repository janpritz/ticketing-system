<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RasaController;

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

// Password reset via OTP
Route::get('/password/forgot', [AuthController::class, 'showForgotForm'])->middleware('guest')->name('password.forgot');
Route::post('/password/otp', [AuthController::class, 'sendOtp'])->middleware('guest','throttle:5,1')->name('password.otp');
Route::get('/password/reset', [AuthController::class, 'showResetForm'])->middleware('guest')->name('password.reset.form');
Route::post('/password/reset', [AuthController::class, 'resetWithOtp'])->middleware('guest','throttle:10,1')->name('password.reset.apply');

Route::get('/tickets/{recepient_id?}', [TicketController::class, 'index'])->name('tickets.index');
Route::get('/tickets/create/{recepient_id?}', [TicketController::class, 'showCreateForm'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->middleware('throttle:10,1')->name('tickets.store');
Route::put('/tickets/{ticket}', [TicketController::class, 'update'])
    ->whereNumber('ticket')
    ->middleware('throttle:10,1')
    ->name('tickets.update');
Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])
    ->whereNumber('ticket')
    ->middleware('throttle:10,1')
    ->name('tickets.destroy');

Route::middleware('auth')->group(function () {
    // Staff dashboard
    Route::get('/staff/dashboard', [StaffController::class, 'index'])->name('staff.dashboard');
    // Live data endpoint for staff dashboard auto-refresh
    Route::get('/staff/dashboard/data', [StaffController::class, 'data'])->middleware('throttle:20,1')->name('staff.dashboard.data');

    // Staff profile
    Route::get('/staff/profile', [StaffController::class, 'profile'])->name('staff.profile');
    Route::post('/staff/profile', [StaffController::class, 'updateProfile'])->name('staff.profile.update');

    // Staff change password (separate flow)
    Route::get('/staff/profile/password', [StaffController::class, 'passwordForm'])->name('staff.profile.password');
    Route::post('/staff/profile/password', [StaffController::class, 'passwordUpdate'])
        ->middleware('throttle:5,1')
        ->name('staff.profile.password.update');

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
    
    // Admin dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    // Live data endpoint for admin dashboard auto-refresh
    Route::get('/admin/dashboard/data', [AdminController::class, 'data'])
        ->middleware('throttle:20,1')
        ->name('admin.dashboard.data');

// Admin user management (CRUD)
Route::prefix('admin/users')->name('admin.users.')->group(function () {
    Route::get('/', [AdminController::class, 'usersIndex'])->name('index');
    Route::get('/create', [AdminController::class, 'usersCreate'])->name('create');
    Route::post('/', [AdminController::class, 'usersStore'])->middleware('throttle:10,1')->name('store');
    Route::get('/{user}/edit', [AdminController::class, 'usersEdit'])->whereNumber('user')->name('edit');
    Route::put('/{user}', [AdminController::class, 'usersUpdate'])->whereNumber('user')->name('update');
    Route::delete('/{user}', [AdminController::class, 'usersDestroy'])->whereNumber('user')->name('destroy');
});

    // Logout (authenticated only)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::post('/send-message', [RasaController::class, 'sendMessage']);