<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketResponseMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendPushNotificationJob;
use App\Jobs\SendTicketForwardJob;

class StaffController extends Controller
{
    // Staff-related methods can be added here in the future
    public function index()
    {
        // Redirect Primary Administrator away from the staff dashboard
        $auth = Auth::user();
        if ($auth && (strtolower((string)($auth->role ?? '')) === 'primary administrator')) {
            return redirect()->route('admin.dashboard');
        }

        $user = $auth;

        // Aggregate ticket stats for this staff member
        $openCount = Ticket::where('staff_id', $user->id)->whereIn('status', ['Open'])->count();
        $inProgressCount = Ticket::where('staff_id', $user->id)->where('status', 'Forwarded')->count();
        $closedCount = Ticket::where('staff_id', $user->id)->where('status', 'Closed')->count();
        $totalCount = Ticket::where('staff_id', $user->id)->count();

        // Overdue tickets: Open tickets older than 1 day
        $overdueDays = env('TICKET_OVERDUE_DAYS', 1);
        $overdueTickets = Ticket::where('staff_id', $user->id)
            ->where('status', 'Open')
            ->where('date_created', '<', now()->subDays($overdueDays))
            ->orderByDesc('date_created')
            ->get();
        $overdueCount = $overdueTickets->count();

        // Recent tickets assigned to this staff (default: only "Open")
        $recentTickets = Ticket::where('staff_id', $user->id)
            ->whereNotIn('status', ['Closed'])
            ->orderByDesc('date_created')
            ->with(['staff', 'routingHistories.staff'])
            ->get();

        return view('dashboards.staff.index', [
            'user' => $user,
            'openCount' => $openCount,
            'inProgressCount' => $inProgressCount,
            'closedCount' => $closedCount,
            'totalCount' => $totalCount,
            'overdueCount' => $overdueCount,
            'overdueTickets' => $overdueTickets,
            'recentTickets' => $recentTickets,
        ]);
    }

    /**
     * Show the Staff Profile page.
     * - Displays current profile info
     * - Activity snapshot (assigned/resolved counts and last 5 tickets)
     */
    public function profile()
    {
        $user = Auth::user();

        $assignedCount = Ticket::where('staff_id', $user->id)->count();
        $resolvedCount = Ticket::where('staff_id', $user->id)->where('status', 'Closed')->count();
        // Last 5 closed tickets for activity list
        $recentTickets = Ticket::where('staff_id', $user->id)
            ->where('status', 'Closed')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        return view('dashboards.staff.profile', [
            'user' => $user,
            'assignedCount' => $assignedCount,
            'resolvedCount' => $resolvedCount,
            'recentTickets' => $recentTickets,
        ]);
    }

    /**
     * Update profile details and photo.
     * - Email is read-only (not editable)
     * - Validates photo type and size, stores in public disk under profile_photos/
     * - Renames photo to user_{id}.ext and deletes old photo if different
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'photo'  => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB
        ]);

        $user->name = $validated['name'];
        $user->category = $validated['category'] ?? $user->category;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'user_' . $user->id . '.' . $ext;
            $dir = 'profile_photos';
            $newPath = $dir . '/' . $filename;

            // If existing photo and different file, delete old
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo) && $user->profile_photo !== $newPath) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Store the new file
            Storage::disk('public')->putFileAs($dir, $file, $filename);
            $user->profile_photo = $newPath;
        }

        $user->save();

        return redirect()->route('staff.profile')->with('status', 'Profile updated successfully.');
    }

    /**
     * Change Password form
     */
    public function passwordForm()
    {
        $user = Auth::user();
        return view('dashboards.staff.password', ['user' => $user]);
    }

    /**
     * Handle Change Password
     */
    public function passwordUpdate(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('staff.profile')->with('status', 'Password changed successfully.');
    }

    /**
     * Build dynamic weekly throughput (last 7 days) for a staff user.
     * Returns:
     * [
     *   'series' => [c1,...,c7],
     *   'labels' => ['Sun','Mon',...],
     *   'max'    => maxCount
     * ]
     */
    private function buildWeeklyThroughput(int $staffId): array
    {
        // Weekly analytics (Mon–Sun) scoped to the signed-in staff's tickets
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $rows = Ticket::where('staff_id', $staffId)
            ->whereBetween('date_created', [$startOfWeek, $endOfWeek])
            ->selectRaw('DATE(date_created) as d, COUNT(*) as c')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $series = [];
        $labels = [];
        $max = 0;

        $cursor = $startOfWeek->copy();
        for ($i = 0; $i < 7; $i++) {
            $dayKey = $cursor->toDateString();
            $count = (int)($rows[$dayKey] ?? 0);
            $series[] = $count;
            $labels[] = $cursor->format('D'); // Mon, Tue, ...
            if ($count > $max) {
                $max = $count;
            }
            $cursor->addDay();
        }

        return [
            'series' => $series,
            'labels' => $labels,
            'max' => $max,
        ];
    }

    /**
     * Live data endpoint for the staff dashboard.
     * Returns counts, tickets, and weekly throughput for the authenticated staff.
     */
    public function data(Request $request)
    {
        $auth = Auth::user();
        // Block Primary Administrator from the staff dashboard data endpoint
        if ($auth && (strtolower((string)($auth->role ?? '')) === 'primary administrator')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = $auth;
        $search = $request->query('search', '');

        // KPI counts (across all statuses)
        $openCount = Ticket::where('staff_id', $user->id)->whereIn('status', ['Open'])->count();
        $inProgressCount = Ticket::where('staff_id', $user->id)->where('status', 'Forwarded')->count();
        $closedCount = Ticket::where('staff_id', $user->id)->where('status', 'Closed')->count();
        $totalCount = Ticket::where('staff_id', $user->id)->count();

        // Overdue tickets: Open tickets older than 1 day
        $overdueDays = env('TICKET_OVERDUE_DAYS', 1);
        $overdueTickets = Ticket::where('staff_id', $user->id)
            ->where('status', 'Open')
            ->where('date_created', '<', now()->subDays($overdueDays))
            ->orderByDesc('date_created')
            ->get();
        $overdueCount = $overdueTickets->count();

        $query = Ticket::where('staff_id', $user->id)
            ->whereIn('status', ['Open', 'Forwarded'])
            ->orderByDesc('date_created')
            ->with(['staff', 'routingHistories.staff']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', '%' . $search . '%')
                  ->orWhere('response', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        // Pagination support for table view
        $perPage = min(max((int) $request->query('perPage', 10), 1), 50);
        $page    = max((int) $request->query('page', 1), 1);

        $paginated     = $query->paginate($perPage, ['*'], 'page', $page);
        $recentTickets = $paginated->items();

        return response()->json([
            'openCount'        => $openCount,
            'inProgressCount'  => $inProgressCount,
            'closedCount'      => $closedCount,
            'totalCount'       => $totalCount,
            'overdueCount'     => $overdueCount,
            'recentTickets'    => $recentTickets,
            'pagination'       => [
                'currentPage' => $paginated->currentPage(),
                'lastPage'    => $paginated->lastPage(),
                'perPage'     => $paginated->perPage(),
                'total'       => $paginated->total(),
            ],
        ]);
    }

    /**
     * Show the Staff Tickets page.
     * Renders the view for staff to manage their assigned tickets.
     */
    public function tickets()
    {
        $auth = Auth::user();
        
        // Redirect Primary Administrator away from the staff tickets page
        if ($auth && (strtolower((string)($auth->role ?? '')) === 'primary administrator')) {
            return redirect()->route('admin.dashboard');
        }

        return view('staff.tickets.index', [
            'user' => $auth,
        ]);
    }

    /**
     * Data endpoint for the staff tickets page.
     * Returns paginated ticket data with status counts and filtering support.
     */
    public function ticketsData(Request $request)
    {
        $auth = Auth::user();
        // Block Primary Administrator from the staff tickets data endpoint
        if ($auth && (strtolower((string)($auth->role ?? '')) === 'primary administrator')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = $auth;
        
        // Get query parameters
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');
        $page = max((int) $request->query('page', 1), 1);
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $sortBy = $request->query('sort_by', 'date_created');
        $sortDirection = strtolower($request->query('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Get counts for each status filter
        $allCount = Ticket::where('staff_id', $user->id)->count();
        $openCount = Ticket::where('staff_id', $user->id)->where('status', 'Open')->count();
        $forwardedCount = Ticket::where('staff_id', $user->id)->where('status', 'Forwarded')->count();
        $closedCount = Ticket::where('staff_id', $user->id)->where('status', 'Closed')->count();

        // Build the base query
        $query = Ticket::where('staff_id', $user->id)
            ->with(['staff', 'routingHistories.staff']);

        // Apply status filtering
        if (in_array($status, ['open', 'forwarded', 'closed'])) {
            $query->where('status', ucfirst($status));
        }
        // 'all' status doesn't need additional where clause

        // Apply search filtering
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', '%' . $search . '%')
                  ->orWhere('response', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        // Apply sorting
        $allowedSortFields = ['date_created', 'updated_at', 'status', 'category', 'email'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('date_created', 'desc');
        }

        // Paginate the results
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);
        $tickets = $paginated->items();

        return response()->json([
            'tickets' => $tickets,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
            'filters' => [
                'status' => $status,
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
            'counts' => [
                'all' => $allCount,
                'open' => $openCount,
                'forwarded' => $forwardedCount,
                'closed' => $closedCount,
            ],
        ]);
    }

    /**
     * Forward a ticket to a staff member matching the provided role
     * and record the routing history.
     */
    public function forward(Request $request, Ticket $ticket)
    {
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        /** @var \App\Models\User|null $auth */
        $auth = Auth::user();

        // Only currently assigned staff or Primary Administrator can reroute
        if ($ticket->staff_id !== $auth->id
            && strtolower((string)($auth->role ?? '')) !== 'primary administrator'
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Reject rerouting if ticket is already closed
        if ($ticket->status === 'Closed') {
            return response()->json(['error' => 'Cannot reroute a closed ticket'], 422);
        }

        // Find the target staff member by user ID
        $newStaff = User::find($request->input('user_id'));

        if (!$newStaff) {
            return response()->json(['error' => 'Staff member not found'], 422);
        }

        // Dispatch job to handle ticket forwarding asynchronously
        SendTicketForwardJob::dispatch(
            $ticket->id,
            $newStaff->id,
            $auth->id,
            $request->input('notes')
        );

        return response()->json([
            'message' => 'Ticket forwarding initiated',
            'new_staff' => $newStaff
        ]);
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
    public function showTicket(Ticket $ticket)
    {
        $auth = Auth::user();
        if (!$auth) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        // Only assigned staff or Primary Administrator may view this page
        if ($ticket->staff_id !== $auth->id
            && ! (strtolower((string)($auth->role ?? '')) === 'primary administrator')
        ) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            abort(403);
        }

        // Load relations needed by the view or JSON response
        $ticket->load(['staff', 'routingHistories.staff']);

        // If it's an AJAX request wanting JSON, return JSON response
        if (request()->ajax() || request()->wantsJson()) {
            // Get list of users with roles for forwarding functionality
            $users = \App\Models\User::whereHas('role')->select('id', 'name')->orderBy('name')->get();
            
            // Return ticket data with users for frontend functionality
            return response()->json(array_merge($ticket->toArray(), ['users' => $users]));
        }

        // Regular request - return HTML view
        return view('dashboards.staff.ticket', [
            'ticket' => $ticket,
        ]);
    }
    public function respond(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string|min:2'
        ]);

        $auth = Auth::user();
        // Only the assigned staff or Primary Administrator may send responses
        if ($ticket->staff_id !== $auth->id
            && strtolower((string)($auth->role ?? '')) !== 'primary administrator'
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Compose and send email
            $responderName = $auth->name ?? 'Staff';
            Mail::to($ticket->email)->send(
                new TicketResponseMail($ticket, $request->input('message'), $responderName)
            );

            // Mark ticket as Closed on successful send and store the response text
            $ticket->response = $request->input('message');
            $ticket->status = 'Closed';
            $ticket->date_closed = now();
            $ticket->save();

            // Record closure in routing history
            TicketRoutingHistory::create([
                'ticket_id' => $ticket->id,
                'staff_id' => $auth->id,
                'status' => 'Closed',
                'routed_at' => now(),
                'notes' => 'Closed via email response',
            ]);

            $ticket->load(['staff', 'routingHistories.staff']);

            return response()->json([
                'message' => 'Response email sent, ticket closed',
                'ticket' => $ticket,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to send email',
                'detail' => $e->getMessage(),
            ], 500);
        }
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
