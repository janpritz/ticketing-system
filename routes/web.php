<?php

/*
|--------------------------------------------------------------------------
| Laravel Framework Imports (Facades/Classes)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminTicketsController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DocumentChangesController;
use App\Http\Controllers\Admin\FAQsController;
use App\Http\Controllers\Admin\KnowledgebaseController;
use App\Http\Controllers\Admin\RasaServerController;
use App\Http\Controllers\Admin\RasaTrainingController;
use App\Http\Controllers\Admin\ReportsController as AdminReportsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicFAQsController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\RasaController;
use App\Http\Controllers\Staff\AnnouncementController as StaffAnnouncementController;
use App\Http\Controllers\Staff\DocumentController as StaffDocumentController;
use App\Http\Controllers\Staff\ReportsController as StaffReportsController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Staff\UploadLogsController;
use App\Http\Controllers\TicketController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Middleware)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('faqs.index');
})->name('home')->middleware('throttle:10,1');

// Service Worker: serve sw.js via Laravel to avoid 404 on some hosts
Route::get('/sw.js', function () {
    return response()->file(base_path('sw.js'), [
        'Content-Type' => 'application/javascript',
    ]);
})->name('sw')->middleware('throttle:10:1');

// Test widget
Route::get('/test-widget', function () {
    return view('test-widget');
})->name('test-widget')->middleware('throttle:10,1');

// Public FAQs
Route::controller(PublicFAQsController::class)->group(function () {
    Route::get('/faqs', 'index')->name('faqs.index');
    Route::get('/api/faqs', 'getApprovedFAQs')->name('api.faqs');
})->middleware('throttle:10,1');

// Static pages
Route::view('/about', 'faqs.about')->name('about')->middleware('throttle:10,1');
Route::view('/contact', 'contact')->name('contact')->middleware('throttle:10,1');

// API chatbot
Route::prefix('api/chatbot')->name('api.chatbot.')->group(function () {
    Route::get('/training-data', [RasaServerController::class, 'getTrainingData'])->name('training-data');
})->middleware('throttle:20,1');

// Push notification test page
Route::view('/push-notification', 'PushNotification.push-test')->name('push-notification')->middleware('throttle:10,1');

// Rasa chatbot message endpoint
Route::post('/send-message', [RasaController::class, 'sendMessage'])->name('rasa.send-message');

/*
|--------------------------------------------------------------------------
| GUEST ONLY ROUTES (Middleware: guest)
|--------------------------------------------------------------------------
*/
Route::middleware(['guest'])->group(function () {
    // Authentication - Login
    Route::controller(AuthController::class)->group(function () {
        Route::get('/admin/login', 'showLoginForm')->name('login');
        Route::post('/admin/login', 'login')->name('login.post');
    })->middleware('throttle:10,1');

    // Password Reset - OTP based
    Route::controller(AuthController::class)->group(function () {
        Route::get('/password/forgot', 'showForgotForm')->name('password.forgot');
        Route::post('/password/otp', 'sendOtp')->name('password.otp');
        Route::get('/password/reset', 'showResetForm')->name('password.reset.form');
        Route::post('/password/reset', 'resetWithOtp')->name('password.reset.apply');
    })->middleware('throttle:10,1');

    // Account Verification (new staff set password)
    Route::controller(AuthController::class)->group(function () {
        Route::get('/verify-account/{token}', 'showVerifyAccountForm')->middleware('throttle:10,1')->name('staff.verify-account');
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
    Route::get('/tickets/verify-otp/{identifier?}', 'showVerifyOtp')->name('tickets.verify-otp')->middleware(['throttle:10,1']);
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

        // --- GROUP 1: General Staff Access ---
        // These routes require the user to be a staff member but are not tied to a specific ticket ID.
        Route::middleware(['staff', 'throttle:30,1'])->group(function () {

            Route::controller(StaffController::class)->group(function () {
                Route::get('/dashboard', 'index')->name('dashboard');
                Route::get('/dashboard/data', 'data')->middleware('throttle:20,1')->name('dashboard.data');
                Route::get('/tickets', 'tickets')->name('tickets');
                Route::get('/tickets/data', 'ticketsData')->name('tickets.data');
                Route::get('/tickets/recent', 'recentTickets')->name('tickets.recent');

                // Profile & Settings
                Route::get('/profile', 'profile')->name('profile');
                Route::post('/profile', 'updateProfile')->name('profile.update');
                Route::post('/profile/email-notifications', 'updateEmailNotifications')->name('profile.email_notifications');
                Route::get('/profile/password', 'passwordForm')->name('profile.password');
                Route::post('/profile/password', 'passwordUpdate')->middleware('throttle:5,1')->name('profile.password.update');

                // Utilities
                Route::get('/mail/test', 'mailTest')->middleware('throttle:5,1')->name('mail.test');
            });

            // Reports
            Route::get('/reports', [StaffReportsController::class, 'index'])
                ->name('reports.index');

            // Push Notifications
            Route::controller(PushNotificationController::class)->group(function () {
                Route::post('/push/subscribe', 'saveSubscription')->name('push.subscribe');
                Route::post('/push/send', 'sendNotification')->name('push.send');
                Route::post('/push/test', 'sendTest')->name('push.test');
            });
        });

        // --- GROUP 2: Ticket-Specific Security ---
        // These routes use the 'ticket.access' middleware to ensure the staff is assigned 
        // to the ticket or is a Primary Administrator.
        Route::middleware(['staff', 'can.access.ticket', 'throttle:30,1'])->group(function () {
            Route::controller(StaffController::class)->group(function () {
                Route::get('/tickets/{ticket}', 'showTicket')->whereNumber('ticket')->name('tickets.show');
                Route::post('/tickets/{ticket}/respond', 'respond')->whereNumber('ticket')->middleware('throttle:20,1')->name('tickets.respond');
                Route::post('/tickets/{ticket}/forward', 'forward')->whereNumber('ticket')->middleware('throttle:30,1')->name('tickets.forward');
                Route::get('/tickets/{ticket}/permissions', 'ticketPermissions')->name('tickets.permissions');
            });
        });

        // --- GROUP 3: Knowledgebase & Document Management ---
        Route::controller(StaffDocumentController::class)->group(function () {
            Route::get('/document-management', 'index')->name('document_management.index');
            Route::get('/document-management/files', 'filesList')->name('document_management.files');
            Route::get('/document-management/fetch', 'fetchFaqs')->name('document_management.fetch');
            Route::post('/document-management', 'store')->middleware('throttle:20,1')->name('document_management.store');
            Route::post('/document-management/upload', 'uploadDocument')->middleware('throttle:20,1')->name('document_management.upload');
            Route::put('/document-management/{faq}', 'update')->whereNumber('faq')->middleware('throttle:20,1')->name('document_management.update');
            Route::delete('/document-management/{faq}', 'destroy')->whereNumber('faq')->middleware('throttle:20,1')->name('document_management.destroy');
            Route::delete('/document-management/document', 'destroyDocumentByName')->middleware('throttle:20,1')->name('document_management.document.destroy');

            // Announcements
            // 1. Custom Action: Pinning (PUT or POST is acceptable, but POST is common for toggles)
            Route::post('announcements/{id}/pin', [StaffAnnouncementController::class, 'pin'])
                ->whereNumber('id')
                ->name('announcements.pin');

            // 2. The Resource: This handles index, store, show, update, and destroy
            Route::resource('announcements', StaffAnnouncementController::class)
                ->except(['create', 'edit'])
                ->middleware('throttle:10,1');
        })->middleware(['throttle:20,1']);

        // --- GROUP 4: Logs & Testing ---
        Route::get('/document-management/test', function () {
            return view('staff.documents.test');
        })->name('document_management.test')->middleware('throttle:10,1');

        Route::controller(UploadLogsController::class)->group(function () {
            Route::get('/upload-logs', 'index')->name('upload-logs.index');
            Route::post('/upload-logs', 'store')->middleware('throttle:20,1')->name('upload-logs.store');
            Route::get('/upload-logs/download-zip', 'downloadZip')->name('upload-logs.download-zip');
        })->middleware('throttle:20,1');
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
                Route::middleware('throttle:30,1')->group(function () {
                    Route::get('/faqs', 'index')->name('faqs.index');
                    Route::get('/faqs/list', 'list')->name('faqs.list');
                    Route::post('/faqs/update-status', 'updateStatus')->name('faqs.update-status');
                    Route::post('/faqs/process-analysis', 'processAnalysis')->name('faqs.process-analysis');
                });
            });

            //Admin Staff Management
            Route::prefix('users')->name('users.')->group(function () {

                // Tier 1: Read/View (50/min)
                Route::middleware('throttle:50,1')->group(function () {
                    Route::get('/', [AdminStaffController::class, 'index'])->name('index');
                    Route::get('/create', [AdminStaffController::class, 'create'])->name('create');
                    Route::get('/{user}/edit', [AdminStaffController::class, 'edit'])->name('edit');
                    Route::get('/{user}/roles', [AdminStaffController::class, 'usersGetRoles'])->name('roles');
                    Route::get('/check-email', [AdminStaffController::class, 'usersCheckEmail'])->name('check-email');
                });

                // Tier 2: Write/Actions (10/min)
                Route::middleware('throttle:50,1')->group(function () {
                    Route::post('/', [AdminStaffController::class, 'store'])->name('store');
                    Route::put('/{user}', [AdminStaffController::class, 'update'])->name('update');
                    Route::delete('/{user}', [AdminStaffController::class, 'destroy'])->name('destroy');
                    Route::post('/{user}/restore', [AdminStaffController::class, 'usersRestore'])->name('restore');
                    Route::post('/resend-verification', [AdminStaffController::class, 'usersResendVerification'])->name('resend-verification');
                });
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
                        Route::post('/upload-document','uploadDocument')->name('uploadDocument');

                        Route::prefix('{faq}')->whereNumber('faq')->group(function () {
                            Route::get('/', 'knowledgebaseShow')->name('show');
                            Route::get('/revisions', 'knowledgebaseRevisions')->name('revisions');
                        });
                    });

                    // --- WRITE OPERATIONS (State Changes) ---
                    // Applies throttle:20,1 to all routes in this group automatically
                    Route::middleware('throttle:20,1')->group(function () {
                        Route::post('/', 'store')->name('store');
                        Route::post('/update-document', 'updateDocument')->name('update-document');
                        Route::delete('/delete-document', 'deleteDocument')->name('delete-document');

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

                    // --- RESTORE DOCUMENT ROUTE ---
                    Route::post('/restore-document', 'restoreDocument')->name('restore-document');
                });

            // Admin Roles Management
            Route::prefix('roles')->name('roles.')->group(function () {

                // 1. Utility Routes (Place these ABOVE resource to avoid ID collision)
                Route::middleware('throttle:60,1')->group(function () {
                    Route::get('/all', [RolesController::class, 'all'])->name('all');
                    Route::get('/by-department/{departmentId}', [RolesController::class, 'byDepartment'])
                        ->name('by-department');
                });

                // 2. Standard CRUD Resource
                // Throttling store, update, and destroy specifically for write-safety
                Route::resource('/', RolesController::class)
                    ->parameters(['' => 'role']) // Ensures parameter is {role} instead of {/ }
                    ->except(['show'])
                    ->whereNumber('role')
                    ->middleware('throttle:20,1');
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

                    // --- Tier 1: Read-Only Operations (60 requests per minute) ---
                    Route::middleware('throttle:30,1')->group(function () {
                        Route::get('/', function () {
                            $users = User::orderBy('name')->get(['id', 'name']);
                            $roles = Role::orderBy('name')->pluck('name');
                            return view('dashboards.admin.tickets.page', compact('users', 'roles'));
                        })->name('index');

                        Route::get('/list', 'list')->name('list');
                        Route::get('/{ticket}', 'show')->whereNumber('ticket')->name('show');
                    });

                    // --- Tier 2: Write/Action Operations (15 requests per minute) ---
                    Route::middleware('throttle:15,1')->group(function () {
                        Route::post('/', 'store')->name('store');
                        Route::put('/{ticket}', 'update')->whereNumber('ticket')->name('update');
                        Route::delete('/{ticket}', 'destroy')->whereNumber('ticket')->name('destroy');
                        Route::post('/{ticket}/respond', 'respond')->whereNumber('ticket')->name('respond');
                        Route::post('/{ticket}/forward', 'forward')->whereNumber('ticket')->name('forward');
                    });
                });
            });

            // Admin Reports
            Route::controller(AdminReportsController::class)->group(function () {
                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/backlog-trend-data', 'getBacklogTrendDataAjax')->name('backlog-trend-data');
                    Route::get('/closed-tickets-trend-data', 'getClosedTicketsTrendDataAjax')->name('closed-tickets-trend-data');
                    Route::get('/dynamic-data', 'getDynamicDataAjax')->name('dynamic-data');
                    Route::get('/forwards/{staff}', 'getForwardsByStaff')->name('forwards.by-staff');
                });
            });

            // List for AJAX
            Route::get('announcements/list', [AdminAnnouncementController::class, 'list'])->name('announcements.list');

            // Trash
            Route::get('announcements/deleted', [AdminAnnouncementController::class, 'deletedIndex'])->name('announcements.deleted');
            Route::get('announcements/deleted/list', [AdminAnnouncementController::class, 'deletedList'])->name('announcements.deleted.list');
            Route::post('announcements/{announcement}/restore', [AdminAnnouncementController::class, 'restore'])->name('announcements.restore');

            // Custom Action (Needs its own line)
            Route::post('announcement/{id}/pin', [AdminAnnouncementController::class, 'pin'])->name('announcement.pin');

            // Standard Web Resource
            Route::resource('announcements', AdminAnnouncementController::class);

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
                    Route::post('/start-rasa-api', 'startRasaApi')->name('start-rasa-api');
                });
            });

            // Admin Document Changes
            Route::controller(DocumentChangesController::class)->group(function () {
                Route::prefix('document-changes')->name('document-changes.')->group(function () {
                    Route::post('/log', 'log')->name('log');
                    Route::get('/training-status', 'trainingStatus')->name('training-status');
                    Route::get('/check-recent-training', 'checkRecentTraining')->name('check-recent-training');
                });
            });

            Route::controller(RasaTrainingController::class)
                ->prefix('rasa-server')->name('rasa-server.')
                ->middleware('throttle:5,1') // Strict throttling to prevent abuse
                ->group(function () {
                    Route::post('/train-rasa', 'trainRasa')->name('train-rasa');
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
