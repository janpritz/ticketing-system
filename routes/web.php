<?php

/*
|--------------------------------------------------------------------------
| Laravel Framework Imports (Facades/Classes)
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Custom Controllers - Alphabetical Order
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminTicketsController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DocumentChangesController;
use App\Http\Controllers\Admin\FAQsController;
use App\Http\Controllers\Admin\KnowledgebaseController;
use App\Http\Controllers\Admin\RasaServerController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicFAQsController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\RasaController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Staff\StaffKnowledgebaseController;
use App\Http\Controllers\Staff\StaffReportsController;
use App\Http\Controllers\Staff\StaffUploadLogsController;
use App\Http\Controllers\TicketController;

/*
|--------------------------------------------------------------------------
| Other Classes (Requests, Services, Models, etc.)
|--------------------------------------------------------------------------
*/
use App\Models\Role;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Middleware)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('faqs.index');
})->name('home');

// Service Worker: serve sw.js via Laravel to avoid 404 on some hosts
Route::get('/sw.js', function () {
    return response()->file(base_path('sw.js'), [
        'Content-Type' => 'application/javascript',
    ]);
})->name('sw');

// Test widget
Route::get('/test-widget', function () {
    return view('test-widget');
})->name('test-widget');

// Public FAQs
Route::controller(PublicFAQsController::class)->group(function () {
    Route::get('/faqs', 'index')->name('faqs.index');
    Route::get('/api/faqs', 'getApprovedFAQs')->name('api.faqs');
});

// Static pages
Route::view('/about', 'faqs.about')->name('about');
Route::view('/contact', 'contact')->name('contact');

// API chatbot
Route::prefix('api/chatbot')->name('api.chatbot.')->group(function () {
    Route::get('/training-data', [RasaServerController::class, 'getTrainingData'])->name('training-data');
});

// Push notification test page
Route::view('/push-notification', 'PushNotification.push-test')->name('push-notification');

// Rasa chatbot message endpoint
Route::post('/send-message', [RasaController::class, 'sendMessage'])->name('rasa.send-message');

/*
|--------------------------------------------------------------------------
| GUEST ONLY ROUTES (Middleware: guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Authentication - Login
    Route::controller(AuthController::class)->group(function () {
        Route::get('/admin/login', 'showLoginForm')->name('login');
        Route::post('/admin/login', 'login')->middleware('throttle:10,1')->name('login.post');
    });

    // Password Reset - OTP based
    Route::controller(AuthController::class)->group(function () {
        Route::get('/password/forgot', 'showForgotForm')->name('password.forgot');
        Route::post('/password/otp', 'sendOtp')->middleware('throttle:5,1')->name('password.otp');
        Route::get('/password/reset', 'showResetForm')->name('password.reset.form');
        Route::post('/password/reset', 'resetWithOtp')->middleware('throttle:10,1')->name('password.reset.apply');
    });

    // Account Verification (new staff set password)
    Route::controller(AuthController::class)->group(function () {
        Route::get('/verify-account/{token}', 'showVerifyAccountForm')->name('staff.verify-account');
        Route::post('/verify-account', 'verifyAccount')->middleware('throttle:10,1')->name('staff.verify-account.post');
    });
});

/*
|--------------------------------------------------------------------------
| PUBLIC TICKET ROUTES (Email verification + OTP middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['check.verified.email', 'otp.verified'])->group(function () {
    // Ticket creation
    Route::controller(TicketController::class)->group(function () {
        Route::get('/tickets/create/{recepient_id?}', 'showCreateForm')->name('tickets.create');
        Route::post('/tickets', 'store')->middleware('throttle:10,1')->name('tickets.store');
    });

    // Ticket viewing (catch-all)
    Route::get('/tickets/{recepient_id?}', [TicketController::class, 'index'])->name('tickets.index');
});

// Ticket status viewing (public)
Route::get('/tickets/status', [TicketController::class, 'showStatusForm'])->name('tickets.status.form');

// Ticket email/OTP verification routes
Route::controller(TicketController::class)->group(function () {
    Route::get('/tickets/verify-email', 'showVerifyEmail')->name('tickets.verify');
    Route::get('/tickets/verify-otp/{identifier?}', 'showVerifyOtp')->name('tickets.verify-otp');
    Route::post('/tickets/send-otp', 'sendTicketOtp')->middleware('throttle:5,1')->name('tickets.send-otp');
    Route::post('/tickets/verify-otp', 'verifyTicketOtp')->middleware('throttle:10,1')->name('tickets.verify-otp-submit');
    Route::post('/tickets/resend-otp', 'resendTicketOtp')->middleware('throttle:5,1')->name('tickets.resend-otp');
});

// Ticket update/destroy (public - needs ticket ownership verification)
Route::put('/tickets/{ticket}', [TicketController::class, 'update'])
    ->whereNumber('ticket')
    ->middleware('throttle:10,1')
    ->name('tickets.update');

Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])
    ->whereNumber('ticket')
    ->middleware('throttle:10,1')
    ->name('tickets.destroy');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (Middleware: auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware(['web', 'handle.logout.csrf'])
        ->name('logout');

    // Serve ticket attachments
    Route::get('/attachments/{path}', [TicketController::class, 'serveAttachment'])
        ->where('path', '.*')
        ->name('attachments.serve');

    /*
    |--------------------------------------------------------------------------
    | STAFF ROUTES (Prefix: staff)
    |--------------------------------------------------------------------------
    */
    Route::prefix('staff')->name('staff.')->group(function () {
        // Staff Dashboard
        Route::controller(StaffController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/dashboard/data', 'data')->middleware('throttle:20,1')->name('dashboard.data');
            Route::get('/profile', 'profile')->name('profile');
            Route::post('/profile', 'updateProfile')->name('profile.update');
            Route::post('/profile/email-notifications', 'updateEmailNotifications')->name('profile.email_notifications');
            Route::get('/profile/password', 'passwordForm')->name('profile.password');
            Route::post('/profile/password', 'passwordUpdate')->middleware('throttle:5,1')->name('profile.password.update');
        });

        // Staff Tickets
        Route::controller(StaffController::class)->group(function () {
            Route::get('/tickets', 'tickets')->name('tickets');
            Route::get('/tickets/data', 'ticketsData')->name('tickets.data');
            Route::get('/tickets/{ticket}', 'showTicket')->whereNumber('ticket')->name('tickets.show');
            Route::post('/tickets/{ticket}/respond', 'respond')->whereNumber('ticket')->middleware('throttle:20,1')->name('tickets.respond');
            Route::post('/tickets/{ticket}/forward', 'forward')->whereNumber('ticket')->middleware('throttle:30,1')->name('tickets.forward');
        });

        // Staff Knowledgebase / Document Management
        Route::controller(StaffKnowledgebaseController::class)->group(function () {
            Route::get('/document-management', 'index')->name('document_management.index');
            Route::get('/document-management/files', 'filesList')->name('document_management.files');
            Route::get('/document-management/fetch', 'fetchFaqs')->name('document_management.fetch');
            Route::post('/document-management', 'store')->middleware('throttle:20,1')->name('document_management.store');
            Route::post('/document-management/upload', 'uploadDocument')->middleware('throttle:20,1')->name('document_management.upload');
            Route::put('/document-management/{faq}', 'update')->whereNumber('faq')->middleware('throttle:20,1')->name('document_management.update');
            Route::delete('/document-management/{faq}', 'destroy')->whereNumber('faq')->middleware('throttle:20,1')->name('document_management.destroy');
            Route::delete('/document-management/document', 'destroyDocumentByName')->middleware('throttle:20,1')->name('document_management.document.destroy');
        });

        // Staff Document Management Test Page
        Route::get('/document-management/test', function () {
            return view('staff.documents.test');
        })->name('document_management.test');

        // Staff Announcements
        Route::controller(StaffKnowledgebaseController::class)->group(function () {
            Route::get('/announcements', 'announcementsIndex')->name('announcements.index');
            Route::get('/announcements/list', 'announcementsList')->name('announcements.list');
            Route::post('/announcements', 'announcementsStore')->middleware('throttle:10,1')->name('announcements.store');
            Route::put('/announcements/{id}', 'announcementsUpdate')->whereNumber('id')->middleware('throttle:10,1')->name('announcements.update');
            Route::delete('/announcements/{id}', 'announcementsDestroy')->whereNumber('id')->middleware('throttle:10,1')->name('announcements.destroy');
            Route::post('/announcements/pin/{id}', 'announcementsPin')->whereNumber('id')->middleware('throttle:10,1')->name('announcements.pin');
        });

        // Staff Reports
        Route::controller(StaffReportsController::class)->group(function () {
            Route::get('/reports', 'index')->name('reports.index');
        });

        // Staff Upload Logs
        Route::controller(StaffUploadLogsController::class)->group(function () {
            Route::get('/upload-logs', 'index')->name('upload-logs.index');
            Route::post('/upload-logs', 'store')->middleware('throttle:20,1')->name('upload-logs.store');
            Route::get('/upload-logs/download-zip', 'downloadZip')->name('upload-logs.download-zip');
        });

        // Staff Push Notifications
        Route::controller(PushNotificationController::class)->group(function () {
            Route::post('/push/subscribe', 'saveSubscription')->name('push.subscribe');
            Route::post('/push/send', 'sendNotification')->name('push.send');
            Route::post('/push/test', 'sendTest')->name('push.test');
        });

        // SMTP Test
        Route::get('/mail/test', [StaffController::class, 'mailTest'])->middleware('throttle:5,1')->name('mail.test');
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES (Middleware: admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {
            // Admin Dashboard
            Route::controller(AdminController::class)->group(function () {
                Route::middleware('throttle:30,1')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/dashboard', 'index')->name('dashboard');
                    Route::get('/dashboard/data', 'data')->name('dashboard.data');
                    Route::get('/logs', 'logsIndex')->name('logs');
                    Route::get('/categories-by-role', 'categoriesByRole')->name('categories.by-role');
                });
            });

            // Admin FAQs
            Route::controller(FAQsController::class)->group(function () {
                Route::get('/faqs', 'index')->name('faqs.index');
                Route::get('/faqs/list', 'list')->name('faqs.list');
                Route::post('/faqs/update-status', 'updateStatus')->name('faqs.update-status');
                Route::post('/faqs/process-analysis', 'processAnalysis')->name('faqs.process-analysis');
            });

            // Admin Staff Management
            Route::controller(AdminStaffController::class)->prefix('users')->name('users.')->group(function () {
                Route::middleware('throttle:50,1')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'create')->name('create');
                    Route::get('/deleted', 'usersDeletedIndex')->name('deleted');
                    Route::get('/{user}/edit', 'usersEdit')->whereNumber('user')->name('edit');
                    Route::get('/{user}/roles', 'usersGetRoles')->whereNumber('user')->name('roles');
                });

                // State-changing routes (Strict throttling to prevent spam/abuse)
                Route::middleware('throttle:10,1')->group(function () {
                    Route::post('/', 'usersStore')->name('store');
                    Route::put('/{user}', 'usersUpdate')->whereNumber('user')->name('update');
                    Route::delete('/{user}', 'usersDestroy')->whereNumber('user')->name('destroy');
                    Route::post('/{user}/restore', 'usersRestore')->whereNumber('user')->name('restore');
                    Route::post('/resend-verification', 'usersResendVerification')->name('resend-verification');
                });

                // Internal validation (Moderate throttling)
                Route::post('/check-email', 'usersCheckEmail')->middleware('throttle:50,1')->name('check-email');
            });

            // Admin Knowledgebase / Document Management
            Route::controller(KnowledgebaseController::class)
                ->prefix('knowledgebase')
                ->name('knowledgebase.')
                ->group(function () {

                    // --- READ OPERATIONS (Browsing & Fetching) ---
                    Route::middleware('throttle:60,1')->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/list', 'list')->name('list');
                        Route::get('/all-json', 'allJson')->name('all-json');
                        Route::get('/deleted', 'knowledgebaseDeletedIndex')->name('deleted');
                        Route::get('/deleted/list', 'knowledgebaseDeletedList')->name('deleted.list');

                        Route::prefix('{faq}')->whereNumber('faq')->group(function () {
                            Route::get('/', 'knowledgebaseShow')->name('show');
                            Route::get('/revisions', 'knowledgebaseRevisions')->name('revisions');
                        });
                    });

                    // --- WRITE OPERATIONS (State Changes) ---
                    // Applies throttle:20,1 to all routes in this group automatically
                    Route::middleware('throttle:20,1')->group(function () {
                        Route::post('/', 'store')->name('store');

                        Route::prefix('{faq}')->whereNumber('faq')->group(function () {
                            Route::put('/', 'update')->name('update');
                            Route::delete('/', 'destroy')->name('destroy');

                            // Custom Action Routes
                            Route::post('/restore', 'knowledgebaseRestore')->name('restore');
                            Route::post('/undo', 'knowledgebaseUndo')->name('undo');
                            Route::post('/disable', 'knowledgebaseDisable')->name('disable');
                            Route::post('/enable', 'knowledgebaseEnable')->name('enable');

                            // Nested Parameters
                            Route::post('/revert/{revision}', 'knowledgebaseRevert')
                                ->whereNumber('revision')
                                ->name('revert');
                        });
                    });
                });

            // Admin Roles Management
            Route::controller(RolesController::class)->group(function () {
                Route::prefix('roles')->name('roles.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('/{role}/edit', 'edit')->whereNumber('role')->name('edit');
                    Route::put('/{role}', 'update')->whereNumber('role')->name('update');
                    Route::delete('/{role}', 'destroy')->whereNumber('role')->name('destroy');
                    Route::get('/all', 'all')->name('all');
                    Route::get('/by-department/{departmentId}', 'byDepartment')->name('by-department');
                });
            });

            // Admin Departments Management
            Route::resource('departments', DepartmentController::class)
                ->middleware('throttle:30,1')
                ->names('departments')
                ->parameters(['departments' => 'department']);

            // Admin Categories (Legacy - redirect to roles)
            Route::prefix('categories')->name('categories.')->group(function () {
                Route::get('/', function () {
                    return redirect()->route('admin.roles.index');
                })->name('index');
                Route::get('/create', function () {
                    return redirect()->route('admin.roles.create');
                })->name('create');
            });

            // Admin Tickets Management
            Route::controller(AdminTicketsController::class)->group(function () {
                Route::prefix('tickets')->name('tickets.')->group(function () {
                    Route::get('/', function () {
                        $users = User::orderBy('name')->get(['id', 'name']);
                        $roles = Role::orderBy('name')->pluck('name');
                        return view('dashboards.admin.tickets.index', compact('users', 'roles'));
                    })->name('index');
                    Route::get('/list', 'list')->name('list');
                    Route::post('/', 'store')->name('store');
                    Route::get('/{ticket}', 'show')->whereNumber('ticket')->name('show');
                    Route::put('/{ticket}', 'update')->whereNumber('ticket')->name('update');
                    Route::delete('/{ticket}', 'destroy')->whereNumber('ticket')->name('destroy');
                    Route::post('/{ticket}/respond', 'respond')->whereNumber('ticket')->name('respond');
                    Route::post('/{ticket}/forward', 'forward')->whereNumber('ticket')->name('forward');
                });
            });

            // Admin Reports
            Route::controller(ReportsController::class)->group(function () {
                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/backlog-trend-data', 'getBacklogTrendDataAjax')->name('backlog-trend-data');
                    Route::get('/closed-tickets-trend-data', 'getClosedTicketsTrendDataAjax')->name('closed-tickets-trend-data');
                    Route::get('/dynamic-data', 'getDynamicDataAjax')->name('dynamic-data');
                    Route::get('/forwards/{staff}', 'getForwardsByStaff')->name('forwards.by-staff');
                });
            });

            // Standard Web Resource
            Route::resource('announcements', AnnouncementController::class);

            // Custom Action (Needs its own line)
            Route::post('announcement/{id}/pin', [AnnouncementController::class, 'pin'])->name('announcement.pin');

            // Admin Rasa Server Manager
            Route::controller(RasaServerController::class)->group(function () {
                Route::prefix('rasa-server')->name('rasa-server.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/status', 'status')->name('status');
                    Route::get('/training-history', 'trainingHistory')->name('training-history');
                    Route::get('/backup-history', 'backupHistory')->name('backup-history');
                    Route::get('/backup-files/{backupId}', 'backupFiles')->name('backup-files');
                    Route::get('/backup-file-content/{backupId}/{filename}', 'backupFileContent')->name('backup-file-content');
                    Route::get('/models-list', 'modelsList')->name('models-list');
                    Route::post('/start-action-server', 'startActionServer')->name('start-action-server');
                    Route::post('/create-backup', 'createBackup')->name('create-backup');
                    Route::delete('/delete-backup/{backupId}', 'deleteBackup')->name('delete-backup');
                    Route::post('/cleanup-models', 'cleanupModels')->name('cleanup-models');
                    Route::get('/fetch-faqs', 'fetchFaqs')->name('fetch-faqs');
                });
            });

            // Admin Document Changes
            Route::controller(DocumentChangesController::class)->group(function () {
                Route::prefix('document-changes')->name('document-changes.')->group(function () {
                    Route::post('/log', 'log')->name('log');
                    Route::get('/training-status', 'trainingStatus')->name('training-status');
                    Route::get('/check-recent-training', 'checkRecentTraining')->name('check-recent-training');
                    Route::post('/train-rasa', 'trainRasa')->name('train-rasa');
                    Route::post('/start-rasa-api', 'startRasaApi')->name('start-rasa-api');
                });
            });

            // Admin Push Notifications
            Route::controller(PushNotificationController::class)->group(function () {
                Route::post('/push/user/{userId}', 'sendToUser')
                    ->whereNumber('userId')
                    ->name('push.user');
                Route::post('/push/all', 'sendToAll')->name('push.all');
            });
        });
    });
});
