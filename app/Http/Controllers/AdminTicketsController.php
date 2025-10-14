<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketRoutingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\TicketResponseMail;

class AdminTicketsController extends Controller
{
    /**
     * Return paginated list of tickets as JSON for admin UI.
     */
    public function list(Request $request)
    {
        $perPage = (int) $request->query('per_page', 25);
        $page = (int) $request->query('page', 1);

        // Base query with eager staff relation (only load needed staff columns to avoid unnecessary data transfer).
        // Also ensure the query selects tickets.* so joins (used later for sorting) don't pollute the column set.
        $query = Ticket::with([
            // Load staff minimal columns and the related role model for DB-backed roles
            'staff' => function($q) {
                $q->select('id', 'name', 'role_id');
            },
            'staff.role'
        ])->select('tickets.*');

        // Keyword search across common fields
        if ($q = $request->query('q')) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('question', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            });
        }

        // Filter by status
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Filter by assignee name (partial match)
        if ($assignee = $request->query('assignee')) {
            $query->whereHas('staff', function ($qb) use ($assignee) {
                $qb->where('name', 'like', "%{$assignee}%");
            });
        }

        // Filter by staff role (supports Role as a related model; users no longer have a 'role' string column)
        if ($role = $request->query('role')) {
            // filter by the related roles.name via the staff->role relation
            $query->whereHas('staff.role', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Filter by assignee id (exact)
        if ($assigneeId = $request->query('assignee_id')) {
            $query->where('staff_id', $assigneeId);
        }
 
        // Sorting
        $sort = $request->query('sort', 'created_desc');
        switch ($sort) {
            case 'created_asc':
                $query->orderBy('date_created', 'asc');
                break;
            case 'created_desc':
                $query->orderBy('date_created', 'desc');
                break;
            case 'status_asc':
                $query->orderBy('status', 'asc')->orderBy('date_created', 'desc');
                break;
            case 'status_desc':
                $query->orderBy('status', 'desc')->orderBy('date_created', 'desc');
                break;
            case 'assignee_asc':
                // order by related staff name
                $query->leftJoin('users as staff_users', 'tickets.staff_id', '=', 'staff_users.id')
                      ->orderBy('staff_users.name', 'asc')
                      ->select('tickets.*');
                break;
            case 'assignee_desc':
                $query->leftJoin('users as staff_users', 'tickets.staff_id', '=', 'staff_users.id')
                      ->orderBy('staff_users.name', 'desc')
                      ->select('tickets.*');
                break;
            default:
                $query->orderBy('date_created', 'desc');
                break;
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', max(1, $page));
 
        // Provide a last_changed timestamp (epoch seconds) so clients can poll efficiently.
        // Prefer a cache key when available to avoid expensive queries.
        $lastChanged = Cache::get('tickets_last_changed');
        if (!$lastChanged) {
            $maxUpdated = Ticket::max('updated_at');
            $lastChanged = $maxUpdated ? strtotime($maxUpdated) : time();
            // seed the cache to avoid repeated DB hits
            Cache::put('tickets_last_changed', $lastChanged, 3600);
        }
 
        // Standardized response structure expected by the frontend
        return response()->json([
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'last_changed' => $lastChanged,
        ]);
    }

    /**
     * Show a single ticket detail as JSON.
     */
    public function show(Request $request, $id)
    {
        // Eager-load minimal related data to avoid N+1 and reduce payload size.
        // Load staff (id, name, role) and recent routing histories (only the fields the UI needs).
        $ticket = Ticket::with([
            'staff:id,name,role',
            'routingHistories' => function ($q) {
                $q->select('id', 'ticket_id', 'staff_id', 'status', 'routed_at', 'notes')
                  ->orderBy('routed_at', 'desc');
            }
        ])->select('tickets.*')->findOrFail($id);
 
        // Normalize a bit for the UI
        return response()->json($ticket);
    }

    /**
     * Respond to a ticket (send response and optionally close).
     * Expects JSON: { message: '...' , close: true|false }
     */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'close' => 'sometimes|boolean',
        ]);

        $ticket = Ticket::findOrFail($id);

        DB::beginTransaction();
        try {
            // Save response on ticket
            $ticket->response = $request->input('message');
            if ($request->boolean('close', true)) {
                $ticket->status = 'Closed';
                $ticket->date_closed = now();
            }
            $ticket->save();

            // Record routing/history entry
            TicketRoutingHistory::create([
                'ticket_id' => $ticket->id,
                'staff_id' => optional(request()->user())->id,
                'status' => $ticket->status,
                'routed_at' => now(),
                'notes' => 'Admin responded via UI',
            ]);

            DB::commit();
 
            // update last-changed cache so other clients can poll efficiently
            try {
                Cache::put('tickets_last_changed', time(), 3600);
            } catch (\Throwable $cacheEx) {
                Log::warning('Failed to update tickets_last_changed cache: ' . $cacheEx->getMessage());
            }
 
            // Attempt to send response email to the ticket owner.
            // We send after committing the DB so the saved response is durable.
            $mailSent = true;
            $mailError = null;
            try {
                if (!empty($ticket->email)) {
                    Mail::to($ticket->email)->send(
                        new TicketResponseMail($ticket, $request->input('message'), optional($request->user())->name)
                    );
                } else {
                    // No recipient email configured on ticket
                    $mailSent = false;
                    $mailError = 'Ticket has no email address';
                }
            } catch (\Throwable $mailEx) {
                // Record the mail error but do not roll back the ticket update.
                $mailSent = false;
                $mailError = $mailEx->getMessage();
                // Optionally log the mail exception for diagnostics
                Log::error('Ticket response email failed: ' . $mailError);
            }
 
            return response()->json([
                'message' => 'Response saved',
                'mail_sent' => $mailSent,
                'mail_error' => $mailError,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to save response', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reroute a ticket to a staff role.
     * Expects JSON: { role: 'Enrollment' }
     */
    public function reroute(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        DB::beginTransaction();
        try {
            // Find a staff with that role (using roles table)
            $staff = User::whereHas('role', function ($q) use ($request) {
                $q->where('name', $request->role);
            })->inRandomOrder()->first();

            $ticket->staff_id = $staff ? $staff->id : null;
            // optional: set status to Re-routed
            $ticket->status = 'Re-routed';
            $ticket->save();

            TicketRoutingHistory::create([
                'ticket_id' => $ticket->id,
                'staff_id' => $ticket->staff_id,
                'status' => $ticket->status,
                'routed_at' => now(),
                'notes' => 'Rerouted by admin to role: ' . $request->role,
            ]);

            DB::commit();
 
            // update last-changed cache
            try {
                Cache::put('tickets_last_changed', time(), 3600);
            } catch (\Throwable $cacheEx) {
                Log::warning('Failed to update tickets_last_changed cache: ' . $cacheEx->getMessage());
            }
 
            return response()->json(['message' => 'Ticket rerouted', 'staff' => $staff]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to reroute', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update ticket fields (currently allows editing question).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'sometimes|string',
            'category' => 'sometimes|string',
            'status' => 'sometimes|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $data = $request->only(['question', 'category', 'status']);
        if (array_key_exists('status', $data) && $data['status'] === 'Closed' && !$ticket->date_closed) {
            $data['date_closed'] = now();
        }

        $ticket->update($data);

        // add routing history for status change or update
        TicketRoutingHistory::create([
            'ticket_id' => $ticket->id,
            'staff_id' => optional(request()->user())->id,
            'status' => $ticket->status,
            'routed_at' => now(),
            'notes' => 'Admin updated ticket',
        ]);
 
        // update last-changed cache
        try {
            Cache::put('tickets_last_changed', time(), 3600);
        } catch (\Throwable $cacheEx) {
            Log::warning('Failed to update tickets_last_changed cache: ' . $cacheEx->getMessage());
        }
 
        return response()->json($ticket);
    }

    /**
     * Delete (soft) a ticket.
     */
    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        // record history
        TicketRoutingHistory::create([
            'ticket_id' => $ticket->id,
            'staff_id' => optional(request()->user())->id,
            'status' => 'Deleted',
            'routed_at' => now(),
            'notes' => 'Deleted by admin',
        ]);
 
        // update last-changed cache
        try {
            Cache::put('tickets_last_changed', time(), 3600);
        } catch (\Throwable $cacheEx) {
            Log::warning('Failed to update tickets_last_changed cache: ' . $cacheEx->getMessage());
        }
 
        return response()->json(['deleted' => true]);
    }
}