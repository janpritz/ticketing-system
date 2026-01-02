<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RasaController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\Admin\RasaServerController;
use App\Http\Controllers\StaffKnowledgebaseController;

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
    return view('login');
});

// Service Worker: serve sw.js via Laravel to avoid 404 on some hosts
Route::get('/sw.js', function () {
    return response()->file(base_path('sw.js'), [
        'Content-Type' => 'application/javascript',
    ]);
})->name('sw');

Route::get('/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest', 'throttle:10,1');

// Password reset via OTP
Route::get('/password/forgot', [AuthController::class, 'showForgotForm'])->middleware('guest')->name('password.forgot');
Route::post('/password/otp', [AuthController::class, 'sendOtp'])->middleware('guest', 'throttle:5,1')->name('password.otp');
Route::get('/password/reset', [AuthController::class, 'showResetForm'])->middleware('guest')->name('password.reset.form');
Route::post('/password/reset', [AuthController::class, 'resetWithOtp'])->middleware('guest', 'throttle:10,1')->name('password.reset.apply');

Route::get('/tickets/{recepient_id?}', [TicketController::class, 'index'])->name('tickets.index');
Route::get('/tickets/verify-email', [TicketController::class, 'showVerifyEmail'])->name('tickets.verify');

Route::get('/tickets/{ticket}', [StaffController::class, 'showTicket'])
    ->whereNumber('ticket')
    ->middleware('auth')
    ->name('tickets.show');
Route::get('/tickets/create/{recepient_id?}', [TicketController::class, 'showCreateForm'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->middleware('throttle:10,1')->name('tickets.store');

// Ticket status viewing routes
Route::get('/tickets/status', [TicketController::class, 'showStatusForm'])->name('tickets.status.form');

Route::put('/tickets/{ticket}', [TicketController::class, 'update'])
    ->whereNumber('ticket')
    ->middleware('throttle:10,1')
    ->name('tickets.update');
Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])
    ->whereNumber('ticket')
    ->middleware('throttle:10,1')
    ->name('tickets.destroy');

    // Staff tickets data endpoint (outside auth for JSON responses)
    Route::get('/staff/tickets/data', [StaffController::class, 'ticketsData'])->name('staff.tickets.data');
    // Live data endpoint for staff dashboard auto-refresh (outside auth for JSON responses)
    Route::get('/staff/dashboard/data', [StaffController::class, 'data'])->middleware('throttle:20,1')->name('staff.dashboard.data');

Route::middleware('auth')->group(function () {
    // Staff dashboard
    Route::get('/staff/dashboard', [StaffController::class, 'index'])->name('staff.dashboard');
    // Individual ticket page for staff (view + respond)
    Route::get('/staff/tickets/{ticket}', [StaffController::class, 'showTicket'])->whereNumber('ticket')->name('staff.tickets.show');
    // Staff tickets index page
    Route::get('/staff/tickets', [StaffController::class, 'tickets'])->name('staff.tickets');

    // Staff profile
    Route::get('/staff/profile', [StaffController::class, 'profile'])->name('staff.profile');
    Route::post('/staff/profile', [StaffController::class, 'updateProfile'])->name('staff.profile.update');

    // Push subscription (web push) - handled by '/staff/push' prefixed routes defined below

    // Push test/send endpoints
    Route::post('/staff/push/test', [PushNotificationController::class, 'sendTest'])
        ->name('staff.push.test');
    Route::post('/admin/push/user/{userId}', [PushNotificationController::class, 'sendToUser'])
        ->whereNumber('userId')
        ->name('admin.push.user');
    Route::post('/admin/push/all', [PushNotificationController::class, 'sendToAll'])
        ->name('admin.push.all');

    // Staff change password (separate flow)
    Route::get('/staff/profile/password', [StaffController::class, 'passwordForm'])->name('staff.profile.password');
    Route::post('/staff/profile/password', [StaffController::class, 'passwordUpdate'])
        ->middleware('throttle:5,1')
        ->name('staff.profile.password.update');

    // Ticket forward (records history)
    Route::post('/staff/tickets/{ticket}/forward', [StaffController::class, 'forward'])
        ->whereNumber('ticket')
        ->middleware('throttle:30,1')
        ->name('staff.tickets.forward');

    // Ticket respond (send email)
    Route::post('/staff/tickets/{ticket}/respond', [StaffController::class, 'respond'])
        ->whereNumber('ticket')
        ->middleware('throttle:20,1')
        ->name('staff.tickets.respond');

    // SMTP test endpoint (sends to the authenticated user's email)
    Route::get('/staff/mail/test', [StaffController::class, 'mailTest'])->middleware('throttle:5,1')->name('staff.mail.test');

    // Admin dashboard
    Route::get('/admin', function () {
        // If the user is authenticated, auto-redirect them to the appropriate dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'Primary Administrator') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('staff.dashboard');
        }

        // Guests still see the public ticket create page
        return view('login');
    });
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

        // Deleted users view + restore
        Route::get('/deleted', [AdminController::class, 'usersDeletedIndex'])->name('deleted');
        Route::post('/{user}/restore', [AdminController::class, 'usersRestore'])->whereNumber('user')->name('restore');
    });

    // Admin FAQ management (CRUD via AJAX)
    Route::prefix('admin/faqs')->name('admin.faqs.')->group(function () {
        Route::get('/', [AdminController::class, 'faqsIndex'])->name('index');
        Route::get('/list', [AdminController::class, 'faqsList'])->name('list');
        Route::post('/', [AdminController::class, 'faqsStore'])->middleware('throttle:20,1')->name('store');
        Route::get('/{faq}', [AdminController::class, 'faqsShow'])->whereNumber('faq')->name('show');
        Route::put('/{faq}', [AdminController::class, 'faqsUpdate'])->whereNumber('faq')->middleware('throttle:20,1')->name('update');
        Route::delete('/{faq}', [AdminController::class, 'faqsDestroy'])->whereNumber('faq')->middleware('throttle:20,1')->name('destroy');

        // Get all FAQs as JSON (for sync)
        Route::get('/all-json', [AdminController::class, 'faqsAllJson'])->name('all-json');

        // Deleted FAQs view + AJAX list (trash)
        Route::get('/deleted', [AdminController::class, 'faqsDeletedIndex'])->name('deleted');
        Route::get('/deleted/list', [AdminController::class, 'faqsDeletedList'])->name('deleted.list');

        // Revisions & revert for FAQ responses (audit / undo)
        Route::get('/{faq}/revisions', [AdminController::class, 'faqsRevisions'])->whereNumber('faq')->name('revisions');
        Route::post('/{faq}/revert/{revision}', [AdminController::class, 'faqsRevert'])->whereNumber('faq')->whereNumber('revision')->name('revert');

        // Restore soft-deleted FAQ
        Route::post('/{faq}/restore', [AdminController::class, 'faqsRestore'])->whereNumber('faq')->name('restore');

        // Undo most recent change for a FAQ
        Route::post('/{faq}/undo', [AdminController::class, 'faqsUndo'])->whereNumber('faq')->name('undo');


        // Disable / Enable FAQ response (used to temporarily unpublish an answer without deleting)
        Route::post('/{faq}/disable', [AdminController::class, 'faqsDisable'])->whereNumber('faq')->middleware('throttle:20,1')->name('disable');
        Route::post('/{faq}/enable', [AdminController::class, 'faqsEnable'])->whereNumber('faq')->middleware('throttle:20,1')->name('enable');
    });

    // Document change tracking and Rasa training
    Route::prefix('admin/document-changes')->name('admin.document-changes.')->group(function () {
        Route::post('/log', [\App\Http\Controllers\Admin\DocumentChangesController::class, 'log'])->name('log');
        Route::get('/training-status', [\App\Http\Controllers\Admin\DocumentChangesController::class, 'trainingStatus'])->name('training-status');
        Route::get('/check-recent-training', [\App\Http\Controllers\Admin\DocumentChangesController::class, 'checkRecentTraining'])->name('check-recent-training');
        Route::post('/train-rasa', [\App\Http\Controllers\Admin\DocumentChangesController::class, 'trainRasa'])->name('train-rasa');
        Route::post('/start-rasa-api', [\App\Http\Controllers\Admin\DocumentChangesController::class, 'startRasaApi'])->name('start-rasa-api');
    });

    // Admin Ticket management (CRUD + respond + reroute) for admin UI (AJAX)
    Route::prefix('admin/tickets')->name('admin.tickets.')->group(function () {
        // paginated listing (JSON)
        Route::get('/list', [\App\Http\Controllers\AdminTicketsController::class, 'list'])->name('list');
        // index page (blade) - renders admin ticket management UI
        Route::get('/', function () {
            // When roles are stored in the DB use Role::pluck('name') instead of deriving from users.
            $users = \App\Models\User::orderBy('name')->get(['id', 'name']);
            $roles = \App\Models\Role::orderBy('name')->pluck('name');
            return view('dashboards.admin.tickets.index', compact('users', 'roles'));
        })->name('index');

        // show single ticket details (JSON)
        Route::get('/{ticket}', [\App\Http\Controllers\AdminTicketsController::class, 'show'])->whereNumber('ticket')->name('show');

        // respond (send email / close)
        Route::post('/{ticket}/respond', [\App\Http\Controllers\AdminTicketsController::class, 'respond'])->whereNumber('ticket')->name('respond');

        // forward to user (records history)
        Route::post('/{ticket}/forward', [\App\Http\Controllers\AdminTicketsController::class, 'forward'])->whereNumber('ticket')->name('forward');

        // update ticket fields (PUT)
        Route::put('/{ticket}', [\App\Http\Controllers\AdminTicketsController::class, 'update'])->whereNumber('ticket')->name('update');

        // delete ticket
        Route::delete('/{ticket}', [\App\Http\Controllers\AdminTicketsController::class, 'destroy'])->whereNumber('ticket')->name('destroy');
    });

    // Admin category management (CRUD)
    Route::prefix('admin/categories')->name('admin.categories.')->group(function () {
        Route::get('/', [CategoriesController::class, 'index'])->name('index');
        Route::get('/create', [CategoriesController::class, 'create'])->name('create');
        Route::post('/', [CategoriesController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoriesController::class, 'edit'])->whereNumber('category')->name('edit');
        Route::put('/{category}', [CategoriesController::class, 'update'])->whereNumber('category')->name('update');
        Route::delete('/{category}', [CategoriesController::class, 'destroy'])->whereNumber('category')->name('destroy');
    });

    // Admin role management (CRUD)
    Route::prefix('admin/roles')->name('admin.roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\RolesController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\RolesController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\RolesController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [\App\Http\Controllers\RolesController::class, 'edit'])->whereNumber('role')->name('edit');
        Route::put('/{role}', [\App\Http\Controllers\RolesController::class, 'update'])->whereNumber('role')->name('update');
        Route::delete('/{role}', [\App\Http\Controllers\RolesController::class, 'destroy'])->whereNumber('role')->name('destroy');
    });

    // Admin reports
    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportsController::class, 'index'])->name('index');
        Route::get('/backlog-trend-data', [\App\Http\Controllers\ReportsController::class, 'getBacklogTrendDataAjax'])->name('backlog-trend-data');
        Route::get('/dynamic-data', [\App\Http\Controllers\ReportsController::class, 'getDynamicDataAjax'])->name('dynamic-data');
    });

    // Admin announcements
    Route::prefix('admin/announcements')->name('admin.announcements.')->group(function () {
        Route::get('/', function () {
            return view('dashboards.admin.announcements.index');
        })->name('index');
        Route::get('/list', [AdminController::class, 'announcementsList'])->name('list');
        Route::post('/', [AdminController::class, 'announcementsStore'])->middleware('throttle:10,1')->name('store');
        Route::put('/{id}', [AdminController::class, 'announcementsUpdate'])->whereNumber('id')->middleware('throttle:10,1')->name('update');
        Route::delete('/{id}', [AdminController::class, 'announcementsDestroy'])->whereNumber('id')->middleware('throttle:10,1')->name('destroy');
        Route::post('/pin/{id}', [AdminController::class, 'announcementsPin'])->whereNumber('id')->middleware('throttle:10,1')->name('pin');
    });

    // Admin Rasa Server Manager
    Route::prefix('admin/rasa-server')->name('admin.rasa-server.')->group(function () {
        Route::get('/', [RasaServerController::class, 'index'])->name('index');
        Route::get('/status', [RasaServerController::class, 'status'])->name('status');
        Route::get('/training-history', [RasaServerController::class, 'trainingHistory'])->name('training-history');
        Route::get('/backup-history', [RasaServerController::class, 'backupHistory'])->name('backup-history');
        Route::get('/backup-files/{backupId}', [RasaServerController::class, 'backupFiles'])->name('backup-files');
        Route::get('/backup-file-content/{backupId}/{filename}', [RasaServerController::class, 'backupFileContent'])->name('backup-file-content');
        Route::get('/models-list', [RasaServerController::class, 'modelsList'])->name('models-list');
        Route::post('/start-action-server', [RasaServerController::class, 'startActionServer'])->name('start-action-server');
        Route::post('/create-backup', [RasaServerController::class, 'createBackup'])->name('create-backup');
        Route::delete('/delete-backup/{backupId}', [RasaServerController::class, 'deleteBackup'])->name('delete-backup');
        Route::post('/cleanup-models', [RasaServerController::class, 'cleanupModels'])->name('cleanup-models');
        Route::get('/fetch-faqs', [RasaServerController::class, 'fetchFaqs'])->name('fetch-faqs');
    });

    // Admin logs
    Route::get('/admin/logs', [AdminController::class, 'logsIndex'])->name('admin.logs');

    // Admin categories by role (for conditional dropdowns)
    Route::get('/admin/categories-by-role', [AdminController::class, 'categoriesByRole'])->name('admin.categories.by-role');

    // Logout (authenticated only)
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware(['web', 'handle.logout.csrf'])
        ->name('logout');


    // Staff FAQs from Rasa server
    Route::get('/staff/faqs/fetch', [StaffController::class, 'fetchFaqs'])->name('staff.faqs.fetch');
    Route::get('/staff/faqs/test', function () {
        return view('staff.faqs.test');
    })->name('staff.faqs.test');

    // Staff reports
    Route::prefix('staff/reports')->name('staff.reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\StaffReportsController::class, 'index'])->name('index');
    });

    // Staff FAQ management
    Route::prefix('staff/faqs')->name('staff.faqs.')->group(function () {
        Route::get('/', [StaffKnowledgebaseController::class, 'index'])->name('index');
        Route::post('/', [StaffKnowledgebaseController::class, 'store'])->middleware('throttle:20,1')->name('store');
        Route::put('/{faq}', [StaffKnowledgebaseController::class, 'update'])->whereNumber('faq')->middleware('throttle:20,1')->name('update');
        Route::delete('/{faq}', [StaffKnowledgebaseController::class, 'destroy'])->whereNumber('faq')->middleware('throttle:20,1')->name('destroy');
    });

    // Staff knowledgebase document upload
    Route::post('/staff/knowledgebase/upload-document', [StaffKnowledgebaseController::class, 'uploadDocument'])->middleware('throttle:10,1')->name('staff.knowledgebase.upload-document');
    Route::get('/staff/knowledgebase/queued-documents', [StaffKnowledgebaseController::class, 'getQueuedDocuments'])->name('staff.knowledgebase.queued-documents');
    Route::delete('/staff/knowledgebase/queued-documents/{id}', [StaffKnowledgebaseController::class, 'cancelQueuedDocument'])->name('staff.knowledgebase.cancel-queued-document');

    // Staff announcements
    Route::prefix('staff/announcements')->name('staff.announcements.')->group(function () {
        Route::get('/', [StaffKnowledgebaseController::class, 'announcementsIndex'])->name('index');
        Route::get('/list', [StaffKnowledgebaseController::class, 'announcementsList'])->name('list');
        Route::post('/', [StaffKnowledgebaseController::class, 'announcementsStore'])->middleware('throttle:10,1')->name('store');
        Route::put('/{id}', [StaffKnowledgebaseController::class, 'announcementsUpdate'])->whereNumber('id')->middleware('throttle:10,1')->name('update');
        Route::delete('/{id}', [StaffKnowledgebaseController::class, 'announcementsDestroy'])->whereNumber('id')->middleware('throttle:10,1')->name('destroy');
        Route::post('/pin/{id}', [StaffKnowledgebaseController::class, 'announcementsPin'])->whereNumber('id')->middleware('throttle:10,1')->name('pin');
    });

    //Push Notification
    // Start Push Notification==========================================================
    Route::view('push-notification', 'PushNotification.push-test');
    Route::prefix('staff/push')->group(function () {
        Route::post('/subscribe', [PushNotificationController::class, 'saveSubscription'])->name('push.subscribe');
        Route::post('/send', [PushNotificationController::class, 'sendNotification'])->name('push.send');
    });
    // End Push Notification==========================================================
});

Route::get('/test-widget', function () {
    return view('test-widget');
});

Route::post('/send-message', [RasaController::class, 'sendMessage']);
