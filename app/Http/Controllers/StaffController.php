<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketResponseMail;

class StaffController extends Controller
{
    // Staff-related methods can be added here in the future
    public function index()
    {
        $user = Auth::user();

        // Aggregate ticket stats for this staff member
        $openCount = Ticket::where('staff_id', $user->id)->whereIn('status', ['Open'])->count();
        $inProgressCount = Ticket::where('staff_id', $user->id)->where('status', 'In-Progress')->count();
        $closedCount = Ticket::where('staff_id', $user->id)->where('status', 'Closed')->count();
        $totalCount = Ticket::where('staff_id', $user->id)->count();

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
            'recentTickets' => $recentTickets,
        ]);
    }

    /**
     * Live data endpoint for the staff dashboard.
     * Returns counts and tickets for the authenticated staff.
     * Query params:
     * - viewAll=true|false  when true returns all tickets (all statuses) for this staff;
     *                       when false returns only 'Open' tickets.
     */
    public function data(Request $request)
    {
        $user = Auth::user();
        $viewAll = filter_var($request->query('viewAll', 'false'), FILTER_VALIDATE_BOOLEAN);

        // KPI counts (across all statuses)
        $openCount = Ticket::where('staff_id', $user->id)->whereIn('status', ['Open'])->count();
        $inProgressCount = Ticket::where('staff_id', $user->id)->where('status', 'In-Progress')->count();
        $closedCount = Ticket::where('staff_id', $user->id)->where('status', 'Closed')->count();
        $totalCount = Ticket::where('staff_id', $user->id)->count();

        $query = Ticket::where('staff_id', $user->id)
            
            ->orderByDesc('date_created')
            ->with(['staff', 'routingHistories.staff']);

        if (!$viewAll) {
            // Only active tickets (Open and In-Progress)
            $query->whereIn('status', ['Open', 'In-Progress', ]);
        }

        // Return list according to filter (no limit to satisfy "fetch all data")
        $recentTickets = $query->get();

        return response()->json([
            'openCount' => $openCount,
            'inProgressCount' => $inProgressCount,
            'closedCount' => $closedCount,
            'totalCount' => $totalCount,
            'recentTickets' => $recentTickets,
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

        $auth = Auth::user();

        // Only currently assigned staff or Primary Administrator can reroute
        if ($ticket->staff_id !== $auth->id && ($auth->role ?? null) !== 'Primary Administrator') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Reject rerouting if ticket is already closed
        if ($ticket->status === 'Closed') {
            return response()->json(['error' => 'Cannot reroute a closed ticket'], 422);
        }

        // Find a staff member with the target role
        $targetRole = $request->input('role');
        $newStaff = User::where('role', $targetRole)->inRandomOrder()->first();

        if (!$newStaff) {
            return response()->json(['error' => 'No staff found for the selected role'], 422);
        }

        // Update ticket assignment and move to In-Progress
        $ticket->staff_id = $newStaff->id;
        $ticket->status = 'In-Progress';
        $ticket->date_closed = null;
        $ticket->save();

        // Record routing history
        TicketRoutingHistory::create([
            'ticket_id' => $ticket->id,
            'staff_id' => $newStaff->id,
            'status' => 'In-Progress',
            'routed_at' => now(),
            'notes' => $request->input('notes')
        ]);

        $ticket->load(['staff', 'routingHistories.staff']);

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
        if ($ticket->staff_id !== $auth->id && ($auth->role ?? null) !== 'Primary Administrator') {
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
