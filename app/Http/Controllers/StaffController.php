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
        $inProgressCount = Ticket::where('staff_id', $user->id)->where('status', 'Re-routed')->count();
        $closedCount = Ticket::where('staff_id', $user->id)->where('status', 'Closed')->count();
        $totalCount = Ticket::where('staff_id', $user->id)->count();

        // Recent tickets assigned to this staff (default: only "Open")
        $recentTickets = Ticket::where('staff_id', $user->id)
            ->whereNotIn('status', ['Closed'])
            ->orderByDesc('date_created')
            ->with(['staff', 'routingHistories.staff'])
            ->get();

        // Weekly throughput for last 7 days (per signed-in staff)
        $weeklyThroughput = $this->buildWeeklyThroughput($user->id);

        return view('dashboards.staff.index', [
            'user' => $user,
            'openCount' => $openCount,
            'inProgressCount' => $inProgressCount,
            'closedCount' => $closedCount,
            'totalCount' => $totalCount,
            'recentTickets' => $recentTickets,
            'weeklyThroughput' => $weeklyThroughput,
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
        $recentTickets = Ticket::where('staff_id', $user->id)
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
     * Query params:
     * - viewAll=true|false  when true returns all tickets (all statuses) for this staff;
     *                       when false returns only 'Open' tickets.
     */
    public function data(Request $request)
    {
        $auth = Auth::user();
        // Block Primary Administrator from the staff dashboard data endpoint
        if ($auth && (strtolower((string)($auth->role ?? '')) === 'primary administrator')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = $auth;
        $viewAll = filter_var($request->query('viewAll', 'false'), FILTER_VALIDATE_BOOLEAN);

        // KPI counts (across all statuses)
        $openCount = Ticket::where('staff_id', $user->id)->whereIn('status', ['Open'])->count();
        $inProgressCount = Ticket::where('staff_id', $user->id)->where('status', 'Re-routed')->count();
        $closedCount = Ticket::where('staff_id', $user->id)->where('status', 'Closed')->count();
        $totalCount = Ticket::where('staff_id', $user->id)->count();

        $query = Ticket::where('staff_id', $user->id)
            ->orderByDesc('date_created')
            ->with(['staff', 'routingHistories.staff']);

        if (!$viewAll) {
            // Only active tickets (Open and Re-routed)
            $query->whereIn('status', ['Open', 'Re-routed']);
        }

        // Pagination support for table view
        $perPage = min(max((int) $request->query('perPage', 10), 1), 50);
        $page    = max((int) $request->query('page', 1), 1);

        $paginated     = $query->paginate($perPage, ['*'], 'page', $page);
        $recentTickets = $paginated->items();

        // Weekly throughput for last 7 days (per signed-in staff)
        $weeklyThroughput = $this->buildWeeklyThroughput($user->id);

        return response()->json([
            'openCount'        => $openCount,
            'inProgressCount'  => $inProgressCount,
            'closedCount'      => $closedCount,
            'totalCount'       => $totalCount,
            'recentTickets'    => $recentTickets,
            'weeklyThroughput' => $weeklyThroughput,
            'pagination'       => [
                'currentPage' => $paginated->currentPage(),
                'lastPage'    => $paginated->lastPage(),
                'perPage'     => $paginated->perPage(),
                'total'       => $paginated->total(),
            ],
        ]);
    }

    /**
     * Reroute a ticket to a staff member matching the provided role
     * and record the routing history.
     */
    public function reroute(Request $request, Ticket $ticket)
    {
        $request->validate([
            'role' => 'required|string'
        ]);

        /** @var \App\Models\User|null $auth */
        $auth = Auth::user();
    
        // Only currently assigned staff or Primary Administrator can reroute
        if ($ticket->staff_id !== $auth->id
            && ! (
                $auth
                && (strtolower((string)($auth->role ?? '')) === 'primary administrator')
            )
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Reject rerouting if ticket is already closed
        if ($ticket->status === 'Closed') {
            return response()->json(['error' => 'Cannot reroute a closed ticket'], 422);
        }

        // Find a staff member with the target role (using roles table)
        $targetRole = $request->input('role');
        $newStaff = User::whereHas('role', function ($q) use ($targetRole) {
            $q->where('name', $targetRole);
        })->inRandomOrder()->first();

        if (!$newStaff) {
            return response()->json(['error' => 'No staff found for the selected role'], 422);
        }

        // Update ticket assignment and move to Re-routed
        $ticket->staff_id = $newStaff->id;
        $ticket->status = 'Re-routed';
        $ticket->date_closed = null;
        $ticket->save();

        // Record routing history
        TicketRoutingHistory::create([
            'ticket_id' => $ticket->id,
            'staff_id' => $newStaff->id,
            'status' => 'Re-routed',
            'routed_at' => now(),
            'notes' => $request->input('notes')
        ]);

        $ticket->load(['staff', 'routingHistories.staff']);

        // Send push to the newly assigned staff if available (non-blocking)
        if ($newStaff && $newStaff->id) {
            try {
                $payload = [
                    'title' => 'Ticket rerouted to you',
                    'body'  => 'Ticket #' . $ticket->id . ' has been rerouted to you.',
                    'data'  => ['url' => '/staff/dashboard']
                ];
                app(\App\Services\PushService::class)->sendToUser($newStaff->id, $payload);
            } catch (\Throwable $e) {
                Log::warning('Push send failed on reroute: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Ticket rerouted successfully',
            'ticket' => $ticket
        ]);
    }

    /**
     * Send a response email to the ticket owner.
     * If sent successfully, close the ticket.
     */
    public function respond(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string|min:2'
        ]);

        $auth = Auth::user();
        // Only the assigned staff or Primary Administrator may send responses
        if ($ticket->staff_id !== $auth->id
            && ! (
                $auth
                && (strtolower((string)($auth->role ?? '')) === 'primary administrator')
            )
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
