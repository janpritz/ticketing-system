<?php

namespace App\Services\Staff;

use App\Models\{User, Ticket};
use App\Jobs\{SendPushNotificationJob, SendTicketForwardJob};
use Illuminate\Support\Facades\{DB, Mail, Log, Auth};
use App\Mail\{TicketProcessedMail, TicketResponseMail};
use Illuminate\Http\JsonResponse;

class TicketService
{
    /**
     * Aggregate stats and fetch tickets specifically for a staff member's dashboard.
     */
    public function getStaffDashboardStats(User $user): array
    {
        $overdueDays = config('app.ticket_overdue_days', 1); // Prefer config() over env()

        // Base query for this specific staff member
        $staffTickets = Ticket::where('staff_id', $user->id);

        // Aggregate Counts
        $stats = [
            'openCount'       => (clone $staffTickets)->where('status', 'Open')->count(),
            'inProgressCount' => (clone $staffTickets)->where('status', 'Forwarded')->count(),
            'closedCount'     => (clone $staffTickets)->where('status', 'Closed')->count(),
            'totalCount'      => (clone $staffTickets)->count(),
        ];

        // Overdue Tickets (Open and older than X days)
        $overdueTickets = (clone $staffTickets)
            ->where('status', 'Open')
            ->where('date_created', '<', now()->subDays($overdueDays))
            ->orderByDesc('date_created')
            ->get();

        // Recent Active Tickets
        $recentTickets = (clone $staffTickets)
            ->whereNotIn('status', ['Closed'])
            ->with(['staff', 'routingHistories.staff', 'role'])
            ->orderByDesc('date_created')
            ->get();

        return array_merge($stats, [
            'overdueCount'   => $overdueTickets->count(),
            'overdueTickets' => $overdueTickets,
            'recentTickets'  => $recentTickets,
        ]);
    }

    /**
     * Retrieve performance metrics and activity history for a specific staff member.
     */
    public function getStaffProfileStats(User $user): array
    {
        $baseQuery = Ticket::where('staff_id', $user->id);

        return [
            'assignedCount' => (clone $baseQuery)->count(),
            'resolvedCount' => (clone $baseQuery)->where('status', 'Closed')->count(),
            'recentTickets' => (clone $baseQuery)
                ->where('status', 'Closed')
                ->orderByDesc('updated_at')
                ->take(5)
                ->get(),
        ];
    }

    /**
     * Retrieve paginated dashboard data with KPI stats for a staff member.
     */
    public function getStaffDashboardData(User $user, array $params): array
    {
        // Reuse the stats logic we built earlier
        $stats = $this->getStaffDashboardStats($user);

        // Build the query for active tickets
        $query = Ticket::where('staff_id', $user->id)
            ->whereIn('status', ['Open', 'Forwarded'])
            ->with(['staff', 'routingHistories.staff', 'role'])
            ->orderByDesc('date_created');

        // Handle Search
        if (!empty($params['search'])) {
            $query->where(function ($q) use ($params) {
                $term = '%' . $params['search'] . '%';
                $q->where('question', 'like', $term)
                    ->orWhere('response', 'like', $term)
                    ->orWhere('status', 'like', $term);
            });
        }

        // Handle Pagination
        $perPage = min(max((int) ($params['perPage'] ?? 10), 1), 50);
        $paginated = $query->paginate($perPage, ['*'], 'page', $params['page'] ?? 1);

        return [
            'openCount'       => $stats['openCount'],
            'inProgressCount' => $stats['inProgressCount'],
            'closedCount'     => $stats['closedCount'],
            'totalCount'      => $stats['totalCount'],
            'overdueCount'    => $stats['overdueCount'],
            'recentTickets'   => $paginated->items(),
            'pagination'      => [
                'currentPage' => $paginated->currentPage(),
                'lastPage'    => $paginated->lastPage(),
                'perPage'     => $paginated->perPage(),
                'total'       => $paginated->total(),
            ],
        ];
    }

    /**
     * Get filtered, sorted, and paginated tickets for a staff member.
     */
    public function getStaffTicketListData(User $user, array $params): array
    {
        // 1. Get filtered counts
        $counts = [
            'all'       => Ticket::where('staff_id', $user->id)->count(),
            'open'      => Ticket::where('staff_id', $user->id)->where('status', 'Open')->count(),
            'forwarded' => Ticket::where('staff_id', $user->id)->where('status', 'Forwarded')->count(),
            'closed'    => Ticket::where('staff_id', $user->id)->where('status', 'Closed')->count(),
        ];

        // 2. Build Base Query
        $query = Ticket::where('staff_id', $user->id)
            ->with(['staff', 'routingHistories.staff', 'role']);

        // 3. Status Filtering
        if (in_array($params['status'], ['open', 'forwarded', 'closed'])) {
            $query->where('status', ucfirst($params['status']));
        }

        // 4. Search Filtering
        if (!empty($params['search'])) {
            $query->where(function ($q) use ($params) {
                $term = '%' . $params['search'] . '%';
                $q->where('question', 'like', $term)
                    ->orWhere('response', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhereHas('role', fn($rq) => $rq->where('name', 'like', $term));
            });
        }

        // 5. Advanced Sorting
        // Priority: Open (1) -> Forwarded (2) -> Closed (3)
        $query->orderByRaw("CASE WHEN status = 'Open' THEN 1 WHEN status = 'Forwarded' THEN 2 ELSE 3 END");

        if ($params['sort_by'] === 'role') {
            $query->leftJoin('roles', 'tickets.role_id', '=', 'roles.id')
                ->select('tickets.*')
                ->orderBy('roles.name', $params['sort_direction']);
        } else {
            $allowed = ['id', 'date_created', 'updated_at', 'status', 'email', 'question'];
            $sort = in_array($params['sort_by'], $allowed) ? $params['sort_by'] : 'date_created';
            $query->orderBy($sort, $params['sort_direction']);
        }

        // 6. Paginate
        $paginated = $query->paginate($params['per_page'], ['*'], 'page', $params['page']);

        return [
            'tickets'    => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
            'filters'    => $params,
            'counts'     => $counts,
        ];
    }

    /**
     * Execute the forwarding of a ticket and trigger associated notifications.
     */
    public function forwardTicket(Ticket $ticket, User $newStaff, User $sender): void
    {
        // Prevent forwarding to unverified users
        if (!$newStaff->isVerified()) {
            Log::warning('Staff\TicketService::forwardTicket: Target staff is not verified', [
                'ticket_id' => $ticket->id,
                'target_staff_id' => $newStaff->id,
                'target_staff_name' => $newStaff->name,
            ]);
            throw new \InvalidArgumentException('Cannot forward ticket to an unverified user. The staff member must verify their email first.');
        }

        DB::transaction(function () use ($ticket, $newStaff, $sender) {
            // 1. Update Ticket State
            $ticket->update([
                'staff_id'    => $newStaff->id,
                'status'      => 'Forwarded',
                'date_closed' => null,
            ]);

            // 2. Record History
            $ticket->routingHistories()->create([
                'staff_id'  => $newStaff->id,
                'status'    => 'Forwarded',
                'routed_at' => now(),
                'notes'     => 'Forwarded by staff to user: ' . $newStaff->name,
            ]);
        });

        // 3. Dispatch Background Jobs
        // Note: It's better to put all email logic inside one Job or use Events
        SendTicketForwardJob::dispatch(
            $ticket->id,
            $newStaff->id,
            $sender->id,
            'Forwarded by staff to user: ' . $newStaff->name
        );

        // 4. Trigger Notifications
        $this->notifyPartiesOfForward($ticket, $newStaff, $sender);
    }

    /**
     * Handle notification logic for forwarding (Can be moved to a Listener).
     */
    protected function notifyPartiesOfForward(Ticket $ticket, User $newStaff, User $sender): void
    {
        // Get the original staff ID before the update
        $originalStaffId = $ticket->staff_id;
        $isInitialAssignment = $originalStaffId === null;

        // Notify New Staff
        if ($newStaff->email && $newStaff->email_notifications) {
            Mail::to($newStaff->email)->queue(new \App\Mail\TicketAssignedMail($ticket, 'forwarded'));
        }

        // Notify Ticket Creator
        if ($ticket->email) {
            if ($isInitialAssignment) {
                // Send "Ticket Assigned" email to customer
                Mail::to($ticket->email)->queue(new \App\Mail\TicketAssignedCustomerMail($ticket, $newStaff));
            } else {
                // Send "Ticket Forwarded" email to customer
                // Load relationships needed for the mailer
                $newStaff->loadMissing('roles');
                $sender->loadMissing('roles');

                Mail::to($ticket->email)->queue(new TicketProcessedMail($ticket, $sender, $newStaff, true));
            }
        }
    }

    /**
     * Record the first time a staff member views a ticket and notify the creator.
     */
    public function markTicketAsViewed(Ticket $ticket, User $auth): void
    {
        // Only proceed if it hasn't been viewed yet and there is an email to notify
        if ($ticket->first_viewed_at || empty($ticket->email)) {
            return;
        }

        $ticket->update([
            'first_viewed_at' => now(),
            'first_viewed_by' => $auth->id,
        ]);

        // Send notification in the background
        try {
            $histories = $ticket->routingHistories->reverse();
            $firstEntry = $histories->first();
            $lastEntry = $histories->last();

            $firstAssignee = $firstEntry ? User::find($firstEntry->staff_id) : null;
            $currentAssignee = $lastEntry ? User::find($lastEntry->staff_id) : $ticket->staff;

            // Queue the mail so the staff member doesn't wait for the email to send
            Mail::to($ticket->email)->queue(new TicketProcessedMail(
                $ticket,
                $firstAssignee,
                $currentAssignee,
                false,
                $auth
            ));
        } catch (\Throwable $e) {
            Log::error("Notification failed for Ticket #{$ticket->id}: " . $e->getMessage());
        }
    }

    public function ticketPermissions(Ticket $ticket, User $user): bool
    {
        return $ticket->staff_id === $user->id || $user->isPrimaryAdmin();
    }

    /**
     * Process the response, send the email, and mark the ticket as closed.
     */
    public function resolveTicketWithResponse(Ticket $ticket, User $auth, string $message): void
    {
        // 1. Send the email first. 
        // (If this fails, the catch block in controller prevents DB from updating)
        $responderName = $auth->name ?? 'Staff';
        Mail::to($ticket->email)->send(
            new TicketResponseMail($ticket, $message, $responderName)
        );

        // 2. Wrap database changes in a transaction
        DB::transaction(function () use ($ticket, $auth, $message) {
            // Update ticket
            $ticket->update([
                'response'    => $message,
                'status'      => 'Closed',
                'date_closed' => now(),
            ]);

            // Record History
            $ticket->routingHistories()->create([
                'staff_id'  => $auth->id,
                'status'    => 'Closed',
                'routed_at' => now(),
                'notes'     => 'Closed via email response',
            ]);
        });
    }

    /**
     * Get paginated recent tickets for a specific staff member.
     */
    public function getRecentTicketsForStaff(User $user, string $search = '', int $perPage = 10): array
    {
        $query = Ticket::where('staff_id', $user->id)
            ->with(['staff', 'routingHistories.staff', 'role'])
            ->orderByDesc('date_created');

        // Apply Search Filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where('question', 'like', $term)
                    ->orWhere('response', 'like', $term)
                    ->orWhereHas('role', fn($rq) => $rq->where('name', 'like', $term));
            });
        }

        $paginated = $query->paginate($perPage);

        return [
            'tickets' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ];
    }
}
