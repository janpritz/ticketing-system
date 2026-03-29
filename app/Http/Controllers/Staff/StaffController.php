<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use App\Models\{Ticket, Role, User};
use App\Mail\{TicketResponseMail};
use Illuminate\Support\Facades\{Log, Mail, Auth};
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\TicketResponseRequest;
use App\Http\Requests\Staff\EmailUpdateNotifRequest;
use App\Http\Requests\Staff\TicketForwardRequest;
use App\Http\Requests\Staff\UpdateProfileRequest;
use App\Services\Staff\{TicketService, UserService};

class StaffController extends Controller
{
    // Staff-related methods can be added here in the future
    public function index(TicketService $service)
    {
        $user = Auth::user();

        // 2. Fetch Aggregated Data from Service
        $dashboardData = $service->getStaffDashboardStats($user);

        // 3. Merge in global data and return view
        return view('dashboards.staff.index.page', array_merge($dashboardData, [
            'user'  => $user,
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]));
    }

    /**
     * Display the staff member's profile and performance summary.
     */
    public function profile(TicketService $service)
    {
        $user = Auth::user();

        // 1. Fetch performance stats and recent activity from the Service
        $profileData = $service->getStaffProfileStats($user);

        // 2. Load role-specific categories
        $categories = $user->role_id
            ? Role::where('role_id', $user->role_id)->orderBy('name')->get()
            : collect();

        return view('dashboards.staff.profile', array_merge($profileData, [
            'user'                => $user,
            'categories_for_role' => $categories,
        ]));
    }

    /**
     * Update profile details and photo.
     * - Email is read-only (not editable)
     * - Validates photo type and size, stores in public disk under profile_photos/
     * - Renames photo to user_{id}.ext and deletes old photo if different
     */
    /**
     * Update the staff member's profile information and photo.
     */
    public function updateProfile(UpdateProfileRequest $request, UserService $userService)
    {
        $user = Auth::user();

        // Delegate the file handling and database update to the service
        $userService->updateStaffProfile($user, $request->validated(), $request->file('photo'));

        return redirect()->route('staff.profile')
            ->with('status', 'Profile updated successfully.');
    }

    /**
     * Persist staff email notification preference (AJAX endpoint)
     */
    /**
     * Toggle email notification settings for the authenticated user.
     */
    public function updateEmailNotifications(EmailUpdateNotifRequest $request, UserService $userService)
    {
        $user = Auth::user();

        $isEnabled = $userService->toggleNotifications($user->id, $request->validated('enabled'));

        return response()->json([
            'saved'   => true,
            'enabled' => $isEnabled
        ]);
    }

    /**
     * Change Password form
     */
    public function passwordForm()
    {
        return view('dashboards.staff.password', ['user' => Auth::user()]);
    }

    /**
     * Handle Change Password
     */
    /**
     * Update the authenticated user's password.
     */
    public function passwordUpdate(UpdateProfileRequest $request, UserService $userService)
    {
        $user = Auth::user();

        $userService->updateUserPassword($user, $request->validated('password'));

        return redirect()->route('staff.profile')
            ->with('status', 'Password changed successfully.');
    }

    /**
     * Live data endpoint for the staff dashboard.
     * Returns counts, tickets, and weekly throughput for the authenticated staff.
     */
    public function data(Request $request, TicketService $service)
    {
        $user = Auth::user();
        // 2. Fetch Aggregated Data and Paginated List
        $params = [
            'search'  => $request->query('search', ''),
            'perPage' => $request->query('perPage', 10),
            'page'    => $request->query('page', 1),
        ];

        $data = $service->getStaffDashboardData($user, $params);

        return response()->json($data);
    }

    /**
     * Show the Staff Tickets page.
     * Renders the view for staff to manage their assigned tickets.
     */
    public function tickets()
    {
        return view('staff.tickets.index', [
            'user' => Auth::user(),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Data endpoint for the staff tickets page.
     * Returns paginated ticket data with status counts and filtering support.
     */
    public function ticketsData(Request $request, TicketService $service)
    {
        $user = Auth::user();

        // 1. Authorization (Middleware should ideally handle this)
        if (!$user || $user->role_id === 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 2. Extract and Sanitize Parameters
        $params = [
            'status'         => $request->query('status', 'all'),
            'search'         => $request->query('search', ''),
            'page'           => max((int) $request->query('page', 1), 1),
            'per_page'       => min(max((int) $request->query('per_page', 15), 1), 100),
            'sort_by'        => $request->query('sort_by', 'date_created'),
            'sort_direction' => strtolower($request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];

        // 3. Delegate to Service
        $result = $service->getStaffTicketListData($user, $params);

        return response()->json($result);
    }
    /**
     * Forward a ticket to another staff member.
     */
    public function forward(TicketForwardRequest $request, Ticket $ticket, TicketService $service)
    {
        $validated = $request->validated();
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        // 1. Check persmissions
        if (!$service->ticketPermissions($ticket, $auth)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $newStaff = User::findOrFail($validated->input('user_id'));

            // 2. Delegate the logic to the Service
            $service->forwardTicket($ticket, $newStaff, $auth);

            return response()->json([
                'message'           => 'Ticket forwarded successfully',
                'new_staff'         => $newStaff,
                'refresh_dashboard' => true
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to forward ticket #{$ticket->id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to forward ticket'], 500);
        }
    }

    /**
     * Send a response email to the ticket owner.
     * If sent successfully, close the ticket.
     */
    /**
     * Show a dedicated ticket detail page for staff (view + response form).
     * Accessible only to the assigned staff member or Primary Administrator.
     * Returns JSON for AJAX requests, HTML view for regular requests.
     */
    /**
     * Display the specific ticket details and record the first view.
     */
    public function showTicket(Ticket $ticket, TicketService $service)
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();
        // 1. Authorization Check
        if (!$service->ticketPermissions($ticket, $auth)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 2. Load Relations
        $ticket->load(['staff', 'routingHistories.staff', 'category']);

        // 3. Mark as Viewed (Logic moved to Service)
        $service->markTicketAsViewed($ticket, $auth);

        // 4. Handle AJAX/JSON Responses
        if (request()->expectsJson()) {
            $users = User::whereHas('roles')->select('id', 'name')->orderBy('name')->get();
            return response()->json(array_merge($ticket->toArray(), ['users' => $users]));
        }

        // 5. Return View
        return view('dashboards.staff.ticket', compact('ticket'));
    }
    /**
     * Respond to a ticket via email and close it.
     */
    public function respond(TicketResponseRequest $request, Ticket $ticket, TicketService $service)
    {
        $validated = $request->validated();
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        // 1. Authorization using your new Model method
        if ($ticket->staff_id !== $auth->id && !$auth->isPrimaryAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // 2. Delegate the business logic to the service
            $service->resolveTicketWithResponse($ticket, $auth, $validated['message']);

            // 3. Load relations for the frontend update
            $ticket->load(['staff', 'routingHistories.staff', 'category']);

            return response()->json([
                'message' => 'Response email sent, ticket closed',
                'ticket'  => $ticket,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to resolve ticket #{$ticket->id}: " . $e->getMessage());
            return response()->json([
                'error'  => 'Failed to send email or update ticket',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retrieve a paginated list of recent tickets for the staff dashboard.
     */
    public function recentTickets(Request $request, TicketService $service)
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();

        // 1. Authorization: Block Primary Admin or Guests
        if (!$auth || $auth->isPrimaryAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 2. Extract parameters
        $search = $request->query('search', '');
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        // 3. Delegate to Service
        $data = $service->getRecentTicketsForStaff($auth, $search, $perPage);

        return response()->json($data);
    }


    /**
     * Send a test email to the authenticated user's email to verify SMTP works.
     * This uses a dummy Ticket instance and the existing TicketResponseMail.
     */
    public function mailTest(Request $request)
    {
        $auth = Auth::user();

        // Build a non-persisted dummy ticket just for email rendering
        $ticket = new Ticket([
            'id' => 9999,
            'category' => 'Diagnostics',
            'question' => 'This is a test message to verify email delivery.',
            'recepient_id' => (string) ($auth->id ?? '0'),
            'email' => $auth->email ?? 'example@example.com',
            'status' => 'Open',
            'date_created' => now(),
        ]);

        try {
            Mail::to($auth->email)->send(
                new TicketResponseMail($ticket, 'SMTP test from Sangkay Ticketing System.', $auth->name ?? 'Staff')
            );

            return response()->json([
                'sent' => true,
                'to' => $auth->email,
                'note' => 'Check your inbox/spam. If not received, verify .env Gmail SMTP and app password.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'sent' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
