<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketRoutingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Create response directly without caching
        $response = (function () use ($request, $perPage, $page) {
            // Base query with eager staff relation (only load needed staff columns to avoid unnecessary data transfer).
            // Also ensure the query selects tickets.* so joins (used later for sorting) don't pollute the column set.
            $query = Ticket::with([
                // Load staff minimal columns and the related role model for DB-backed roles
                'staff' => function($q) {
                    $q->select('id', 'name', 'role_id');
                },
                // load category relation so the frontend can render category.name without an extra query
                'category',
                'staff.role'
            ])->select('tickets.*');

            // Keyword search across common fields
            if ($q = $request->query('q')) {
                $query->where(function ($qBuilder) use ($q) {
                    $qBuilder->where('question', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        // category is now a relation (categories.name); search via whereHas to match name
                        ->orWhereHas('category', function ($catQ) use ($q) {
                            $catQ->where('name', 'like', "%{$q}%");
                        });
                });
            }

            // Filter by status
            if ($status = $request->query('status')) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
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
            // Always query DB for fresh data.
            $maxUpdated = Ticket::max('updated_at');
            $lastChanged = $maxUpdated ? strtotime($maxUpdated) : time();

            // Convert items to arrays and attach a normalized category_name field so the frontend
            // can consistently render the category name regardless of migration state.
            $items = array_map(function ($m) {
                // $m is a Ticket model instance
                $arr = $m->toArray();
                $arr['category_name'] = isset($m->category) && is_object($m->category) ? ($m->category->name ?? null) : ($m->getAttribute('category') ?? null);
                return $arr;
            }, $paginator->items());

            // Standardized response structure expected by the frontend
            return [
                'items' => $items,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
                'last_changed' => $lastChanged,
            ];
        })();

        return response()->json($response)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Show a single ticket detail as JSON.
     */
    public function show(Request $request, $id)
    {
        // Get ticket detail directly without caching
        $data = (function () use ($id) {
            // Eager-load minimal related data to avoid N+1 and reduce payload size.
            // Load staff (id, name, role_id) and the staff->role relation, plus recent routing histories.
            $ticket = Ticket::with([
                'staff' => function ($q) {
                    // select role_id (foreign key) so the relation can resolve the Role model
                    $q->select('id', 'name', 'role_id');
                },
                'staff.role',
                // ensure category is loaded so the frontend can read category.name
                'category',
                'routingHistories' => function ($q) {
                    $q->select('id', 'ticket_id', 'staff_id', 'status', 'routed_at', 'notes')
                      ->orderBy('routed_at', 'desc');
                }
            ])->select('tickets.*')->findOrFail($id);

            // Get list of users with roles (staff) for rerouting
            $users = User::whereHas('role')->select('id', 'name')->orderBy('name')->get();

            // Normalize a bit for the UI
            $ticketArray = $ticket->toArray();
            // provide a normalized category_name for the frontend modal
            $ticketArray['category_name'] = isset($ticket->category) && is_object($ticket->category) ? ($ticket->category->name ?? null) : ($ticket->getAttribute('category') ?? null);
            // Ensure we're working with arrays only
            if (is_array($ticketArray)) {
                // Convert $users to an array before merging
                $usersArray = $users->toArray();
                return array_merge($ticketArray, ['users' => $usersArray]);
            } else {
                // If toArray() didn't return an array (shouldn't happen, but defensive)
                return ['users' => $users->toArray()];
            }
        })();

        return response()->json($data)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
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
        Log::info('Ticket forward request received', [
            'ticket_id' => $id,
            'user_id' => $request->user_id,
            'current_user' => optional(request()->user())->id,
            'current_user_name' => optional(request()->user())->name,
            'route' => $request->route()->getName(),
            'url' => $request->url(),
            'method' => $request->method()
        ]);

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        $originalStaffId = $ticket->staff_id;
        Log::info('Ticket found for forwarding', ['ticket_id' => $ticket->id, 'ticket_status' => $ticket->status]);

        $user = User::findOrFail($request->user_id);
        Log::info('User found for forwarding', ['forward_to_user_id' => $user->id, 'forward_to_user_name' => $user->name]);

        DB::beginTransaction();
        try {
            // Update ticket assignment and status
            $ticket->staff_id = $request->user_id;
            $ticket->status = 'Forwarded';
            $ticket->date_closed = null; // Reset closed date if it was set
            $ticket->save();

            // Record routing history
            TicketRoutingHistory::create([
                'ticket_id' => $ticket->id,
                'staff_id' => $request->user_id,
                'status' => 'Forwarded',
                'routed_at' => now(),
                'notes' => 'Forwarded by admin to user: ' . $user->name,
            ]);

            DB::commit();

            Log::info('Ticket forward completed successfully', ['ticket_id' => $ticket->id, 'forwarded_to' => $user->name]);

            // Dispatch job for push notification (non-critical)
            SendTicketForwardJob::dispatch(
                $ticket->id,
                $request->user_id,
                optional(request()->user())->id,
                'Forwarded by admin to user: ' . $user->name
            );

            return response()->json(['message' => 'Ticket forwarded successfully', 'staff' => $user, 'refresh_dashboard' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Ticket forward failed', ['error' => $e->getMessage(), 'ticket_id' => $id]);
            return response()->json(['message' => 'Failed to forward ticket', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'sometimes|string',
            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
            'status' => 'sometimes|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $data = $request->only(['question', 'category_id', 'status']);
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


        return response()->json(['deleted' => true]);
    }

    /**
     * Create a new ticket from the admin UI and assign staff based on category_id.
     * This runs the same assignment logic as the queued job but executes it synchronously
     * so admins see immediate assignment results in the UI.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'question' => 'required|string',
            'recepient_id' => ['required'],
            'email' => 'required|email|max:255',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Handle attachments
        $attachmentsPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');
                $attachmentsPaths[] = $path;
            }
        }

        $ticket = new Ticket();
        $ticket->category_id = $request->input('category_id');
        $ticket->question = $request->input('question');
        $ticket->recepient_id = $request->input('recepient_id');
        $ticket->email = $request->input('email');
        $ticket->status = 'Open';
        $ticket->staff_id = null;
        $ticket->date_created = now();
        $ticket->date_closed = null;
        $ticket->attachments = json_encode($attachmentsPaths);
        $ticket->save();

        // Run the assignment logic synchronously so admin sees immediate assignment
        try {
            \App\Jobs\ProcessTicketCreation::dispatchSync($ticket->id, $request->input('category_id'));
        } catch (\Throwable $e) {
            // Log and continue; assignment failed but ticket creation succeeded
            \Illuminate\Support\Facades\Log::error('AdminTicketsController::store - assignment failed: ' . $e->getMessage(), ['ticket_id' => $ticket->id]);
        }

        if ($request->wantsJson()) {
            // reload fresh ticket with relations
            $ticket->load('staff', 'category');
            return response()->json(['ticket' => $ticket], 201);
        }

        // For web requests redirect back to admin tickets UI with status
        return redirect()->route('admin.tickets.index')->with('status', 'Ticket created. Assignment in progress.');
    }
}
