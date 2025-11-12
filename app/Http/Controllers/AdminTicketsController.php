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
use Illuminate\Support\Facades\Storage;
use App\Mail\TicketResponseMail;
use App\Jobs\SendTicketResponseJob;
use App\Jobs\SendTicketForwardJob;
use App\Jobs\SendPushNotificationJob;

class AdminTicketsController extends Controller
{
    /**
     * Return paginated list of tickets as JSON for admin UI.
     */
    public function list(Request $request)
    {
        $perPage = (int) $request->query('per_page', 25);
        $page = (int) $request->query('page', 1);

        // Create a cache key based on request parameters
        $cacheKey = 'tickets_list_' . md5(serialize($request->query()));

        // Cache the entire response for 20 seconds
        $response = Cache::remember($cacheKey, 20, function () use ($request, $perPage, $page) {
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
            return [
                'items' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
                'last_changed' => $lastChanged,
            ];
        });

        return response()->json($response);
    }

    /**
     * Show a single ticket detail as JSON.
     */
    public function show(Request $request, $id)
    {
        $cacheKey = 'ticket_detail_' . $id;

        // Cache the ticket detail for 20 seconds
        $data = Cache::remember($cacheKey, 20, function () use ($id) {
            // Eager-load minimal related data to avoid N+1 and reduce payload size.
            // Load staff (id, name, role_id) and the staff->role relation, plus recent routing histories.
            $ticket = Ticket::with([
                'staff' => function ($q) {
                    // select role_id (foreign key) so the relation can resolve the Role model
                    $q->select('id', 'name', 'role_id');
                },
                'staff.role',
                'routingHistories' => function ($q) {
                    $q->select('id', 'ticket_id', 'staff_id', 'status', 'routed_at', 'notes')
                      ->orderBy('routed_at', 'desc');
                }
            ])->select('tickets.*')->findOrFail($id);

            // Get list of users with roles (staff) for rerouting
            $users = User::whereHas('role')->select('id', 'name')->orderBy('name')->get();

            // Normalize a bit for the UI
            return array_merge($ticket->toArray(), ['users' => $users]);
        });

        return response()->json($data);
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


            // Clear tickets cache on update
            Cache::flush();

            // update last-changed cache so other clients can poll efficiently
            try {
                Cache::put('tickets_last_changed', time(), 3600);
            } catch (\Throwable $cacheEx) {
                Log::warning('Failed to update tickets_last_changed cache: ' . $cacheEx->getMessage());
            }

            // Dispatch job to send response email to the ticket owner.
            // We dispatch after committing the DB so the saved response is durable.
            $mailSent = true;
            $mailError = null;
            if (!empty($ticket->email)) {
                SendTicketResponseJob::dispatch(
                    $ticket->id,
                    $request->input('message'),
                    optional($request->user())->name
                );
            } else {
                // No recipient email configured on ticket
                $mailSent = false;
                $mailError = 'Ticket has no email address';
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
     * Forward a ticket to a specific user.
     * Expects JSON: { user_id: 123 }
     */
    public function forward(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);

        // Dispatch job to handle ticket forwarding asynchronously
        $user = User::findOrFail($request->user_id);
        SendTicketForwardJob::dispatch(
            $ticket->id,
            $request->user_id,
            optional(request()->user())->id,
            'Forwarded by admin to user: ' . $user->name
        );

        // Clear tickets cache on update
        Cache::flush();

        // update last-changed cache
        try {
            Cache::put('tickets_last_changed', time(), 3600);
        } catch (\Throwable $cacheEx) {
            Log::warning('Failed to update tickets_last_changed cache: ' . $cacheEx->getMessage());
        }

        return response()->json(['message' => 'Ticket forwarding initiated', 'staff' => $user]);
    }

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

        // Clear tickets cache on update
        Cache::flush();

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

        // Clear tickets cache on delete
        Cache::flush();

        // update last-changed cache
        try {
            Cache::put('tickets_last_changed', time(), 3600);
        } catch (\Throwable $cacheEx) {
            Log::warning('Failed to update tickets_last_changed cache: ' . $cacheEx->getMessage());
        }

        return response()->json(['deleted' => true]);
    }
}