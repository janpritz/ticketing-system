<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\{DB, Log, Mail, Storage};
use App\Models\{Ticket, User, TicketRoutingHistory};
use App\Mail\TicketProcessedMail;
use App\Jobs\{SendTicketResponseJob, SendTicketForwardJob, ProcessTicketCreation};
use Illuminate\Support\{Str, Collection};

class TicketService
{
    public function getFilteredTickets(array $params): array
    {
        $perPage = (int) ($params['per_page'] ?? 25);
        $page = (int) ($params['page'] ?? 1);

        $query = Ticket::with([
            'staff' => fn($q) => $q->select('id', 'name'),
            'role',
            'staff.userRoles' => fn($q) => $q->where('is_primary_role', true)->with('role')
        ])->select('tickets.*');

        // Apply Search & Filters
        $this->applySearch($query, $params['q'] ?? null);
        $this->applyStatusFilter($query, $params['status'] ?? null);
        $this->applyStaffFilters($query, $params);
        $this->applySorting($query, $params['sort'] ?? 'created_desc');

        $paginator = $query->paginate($perPage, ['*'], 'page', max(1, $page));

        // Get fresh timestamp for polling
        $maxUpdated = Ticket::max('updated_at');

        return [
            'items' => collect($paginator->items())->map(fn($ticket) => $this->normalizeTicket($ticket)),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'last_changed' => $maxUpdated ? strtotime($maxUpdated) : time(),
        ];
    }

    protected function applySearch($query, $q)
    {
        if (!$q) return;

        $query->where(function ($qb) use ($q) {
            $qb->where('question', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhereHas('role', fn($rq) => $rq->where('name', 'like', "%{$q}%"));
        });
    }

    protected function applySorting($query, $sort)
    {
        switch ($sort) {
            case 'assignee_asc':
            case 'assignee_desc':
                $direction = str_contains($sort, 'asc') ? 'asc' : 'desc';
                $query->leftJoin('users as staff_users', 'tickets.staff_id', '=', 'staff_users.id')
                    ->orderBy('staff_users.name', $direction);
                break;
            case 'status_asc':
                $query->orderBy('status', 'asc')->orderBy('date_created', 'desc');
                break;
            default:
                $direction = str_contains($sort, 'asc') ? 'asc' : 'desc';
                $query->orderBy('date_created', $direction);
        }
    }

    protected function normalizeTicket($ticket): array
    {
        $data = $ticket->toArray();
        // Ensure role_name is consistent for the frontend
        $data['role_name'] = $ticket->role->name ?? $ticket->getAttribute('role') ?? null;
        return $data;
    }

    protected function applyStatusFilter($query, $status): void
    {
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
    }

    protected function applyStaffFilters($query, array $params): void
    {
        // 1. Partial Name Search (e.g., "John")
        if ($assignee = ($params['assignee'] ?? null)) {
            $query->whereHas('staff', function ($qb) use ($assignee) {
                $qb->where('name', 'like', "%{$assignee}%");
            });
        }

        // 2. Exact ID Search
        if ($assigneeId = ($params['assignee_id'] ?? null)) {
            $query->where('staff_id', $assigneeId);
        }

        // 3. Filter by Staff's Primary Role Name
        if ($role = ($params['role'] ?? null)) {
            $query->whereHas('staff.userRoles', function ($q) use ($role) {
                $q->where('is_primary_role', true)
                    ->whereHas('role', function ($roleQ) use ($role) {
                        $roleQ->where('name', $role);
                    });
            });
        }
    }

    /**
     * Records the initial view and sends notification to the creator.
     */
    public function markTicketAsViewed(Ticket $ticket, User $user): void
    {
        if (!$user || !empty($ticket->first_viewed_at) || empty($ticket->email)) {
            return;
        }

        try {
            DB::transaction(function () use ($ticket, $user) {
                $ticket->update([
                    'first_viewed_at' => now(),
                    'first_viewed_by' => $user->id
                ]);

                $this->sendViewedNotification($ticket);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to process ticket view: ' . $e->getMessage());
        }
    }

    /**
     * Returns the ticket data merged with staff lists for the re-routing UI.
     */
    public function getTicketDetails(Ticket $ticket): array
    {
        $ticket->load([
            'staff:id,name',
            'staff.userRoles' => fn($q) => $q->where('is_primary_role', true)->with('role'),
            'role',
            'routingHistories' => fn($q) => $q->orderBy('routed_at', 'desc')
        ]);

        $staffList = User::whereHas('roles')->select('id', 'name')->orderBy('name')->get();

        $data = $ticket->toArray();
        $data['role_name'] = $ticket->role->name ?? $ticket->getAttribute('role');
        $data['users'] = $staffList->toArray();

        return $data;
    }

    protected function sendViewedNotification(Ticket $ticket): void
    {
        $histories = $ticket->role()->orderBy('routed_at', 'asc')->get();

        // Default to the current staff if no history exists
        $firstAssignee = $ticket->staff;
        $currentAssignee = $ticket->staff;

        if ($histories->isNotEmpty()) {
            $firstAssignee = User::find($histories->first()->staff_id);
            $currentAssignee = User::find($histories->last()->staff_id);
        }

        // Eager load roles for the mailer
        $firstAssignee?->load(['userRoles' => fn($q) => $q->where('is_primary_role', true)->with('role')]);
        $currentAssignee?->load(['userRoles' => fn($q) => $q->where('is_primary_role', true)->with('role')]);

        Mail::to($ticket->email)->send(new TicketProcessedMail($ticket, $firstAssignee, $currentAssignee, false));
    }

    /**
     * Processes a staff response, updates ticket status, and dispatches email.
     */
    public function respondToTicket(Ticket $ticket, string $message, bool $shouldClose, User $staff): array
    {
        try {
            DB::transaction(function () use ($ticket, $message, $shouldClose, $staff) {
                // 1. Update Ticket State
                $ticket->response = $message;
                if ($shouldClose) {
                    $ticket->status = 'Closed';
                    $ticket->date_closed = now();
                }
                $ticket->save();

                // 2. Record History
                $ticket->routingHistories()->create([
                    'staff_id' => $staff->id,
                    'status' => $ticket->status,
                    'routed_at' => now(),
                    'notes' => 'Admin responded via UI',
                ]);
            });

            // 3. Handle Email Dispatch (Outside transaction)
            if (!empty($ticket->email)) {
                SendTicketResponseJob::dispatch(
                    $ticket->id,
                    $message,
                    $staff->name
                );
                return ['success' => true, 'message' => 'Response saved and email queued.'];
            }

            return ['success' => true, 'message' => 'Response saved, but ticket has no email.'];
        } catch (\Throwable $e) {
            Log::error("Ticket Response Error [ID: {$ticket->id}]: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to save response.'];
        }
    }

    /**
     * Forwards a ticket, logs history, and dispatches notifications.
     */
    public function forwardTicket(Ticket $ticket, int $newStaffId, User $admin): array
    {
        $originalStaffId = $ticket->staff_id;
        $newStaff = User::findOrFail($newStaffId);

        try {
            DB::transaction(function () use ($ticket, $newStaff, $admin) {
                // 1. Update Ticket State
                $ticket->update([
                    'staff_id' => $newStaff->id,
                    'status'   => 'Forwarded',
                    'date_closed' => null, // Re-open if it was closed
                ]);

                // 2. Record Routing History
                $ticket->routingHistories()->create([
                    'staff_id'  => $newStaff->id,
                    'status'    => 'Forwarded',
                    'routed_at' => now(),
                    'notes'     => "Forwarded by admin ({$admin->name}) to user: {$newStaff->name}",
                ]);
            });

            // 3. Post-Transaction Side Effects
            $this->dispatchForwardingNotifications($ticket, $newStaff, $originalStaffId, $admin);

            return [
                'success' => true,
                'message' => 'Ticket forwarded successfully',
                'staff'   => $newStaff,
                'refresh_dashboard' => true
            ];
        } catch (\Throwable $e) {
            Log::error("Ticket Forward Error [ID: {$ticket->id}]: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to forward ticket.'];
        }
    }

    protected function dispatchForwardingNotifications(Ticket $ticket, User $newStaff, ?int $oldStaffId, User $admin): void
    {
        try {
            // A. Push Notification Job
            SendTicketForwardJob::dispatch($ticket->id, $newStaff->id, $admin->id, "Forwarded to {$newStaff->name}");

            // B. Notify the New Assignee
            if (!empty($newStaff->email) && $newStaff->email_notifications) {
                Mail::to($newStaff->email)->send(new \App\Mail\TicketAssignedMail($ticket, 'forwarded'));
            }

            // C. Notify the Ticket Creator (Customer)
            if (!empty($ticket->email)) {
                $firstAssignee = $oldStaffId ? User::find($oldStaffId) : null;

                // Eager load roles for the mailer
                $loadRole = fn($q) => $q->where('is_primary_role', true)->with('role');
                $firstAssignee?->load(['userRoles' => $loadRole]);
                $newStaff->load(['userRoles' => $loadRole]);

                Mail::to($ticket->email)->send(new TicketProcessedMail($ticket, $firstAssignee, $newStaff, true));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch forwarding notifications for ticket [ID: {$ticket->id}]: " . $e->getMessage());
        }
    }

    public function handleDeleteTicket(Ticket $ticket): void
    {
        try {
            $ticket->delete();

            // record history
            TicketRoutingHistory::create([
                'ticket_id' => $ticket->id,
                'staff_id' => optional(request()->user())->id,
                'status' => 'Deleted',
                'routed_at' => now(),
                'notes' => 'Deleted by admin',
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to delete ticket [ID: {$ticket->id}]: " . $e->getMessage());
        }
    }

    /**
     * Handles file uploads, ticket persistence, and assignment logic.
     */
    public function createTicket(array $data, ?array $files): Ticket
    {
        // 1. Handle File Uploads
        $attachmentPaths = [];
        if ($files) {
            foreach ($files as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');
                $attachmentPaths[] = $path;
            }
        }

        // 2. Create the Ticket
        $ticket = Ticket::create([
            'role_id'      => $data['role_id'],
            'question'     => $data['question'],
            'recepient_id' => $data['recepient_id'],
            'email'        => $data['email'],
            'status'       => 'Open',
            'date_created' => now(),
            'attachments'  => json_encode($attachmentPaths),
        ]);

        // 3. Immediate Assignment Logic
        try {
            ProcessTicketCreation::dispatchSync($ticket->id, $data['role_id']);
        } catch (\Throwable $e) {
            Log::error("Ticket Assignment Failed [ID: {$ticket->id}]: " . $e->getMessage());
        }

        return $ticket;
    }

    public function resolveAttachmentPath(string $path): array
    {
        // 1. Directory Traversal Protection
        if (!Str::startsWith($path, 'attachments/')) {
            return ['exists' => false];
        }

        $disk = Storage::disk('public');

        // 2. Physical File Check
        if (!$disk->exists($path)) {
            return ['exists' => false];
        }

        return [
            'exists'    => true,
            'full_path' => $disk->path($path)
        ];
    }

    /**
     * Validates if an OTP session exists and is still fresh for a given identifier.
     */
    public function checkOtpSession(string $email): array
    {
        $sessionKey = "otp_verified_{$email}";
        $otpData = session($sessionKey);

        // No session exists
        if (!$otpData) {
            return [
                'valid' => false,
                'error_type' => 'info',
                'message' => 'Please verify your identity via OTP.'
            ];
        }

        // Check expiration (30 minutes)
        $verifiedAt = $otpData['verified_at'] ?? null;
        if (!$verifiedAt || now()->diffInMinutes($verifiedAt) > 30) {
            session()->forget($sessionKey);
            return [
                'valid' => false,
                'error_type' => 'info',
                'message' => 'Your session has expired. Please verify again.'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Handles file storage, database persistence, and initial job processing.
     */
    public function createAndProcessTicket(array $data, array $files): Ticket
    {
        // 1. Handle File Uploads
        $attachmentsPaths = [];
        foreach ($files as $file) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $attachmentsPaths[] = $file->storeAs('attachments', $filename, 'public');
        }

        // 2. Persist Ticket
        $ticket = Ticket::create([
            'category_id'  => $data['role_id'] ?? null,
            'question'     => $data['question'],
            'recepient_id' => $data['recepient_id'],
            'email'        => $data['email'],
            'status'       => 'Open',
            'date_created' => now(),
            'attachments'  => json_encode($attachmentsPaths),
        ]);

        // 3. Attempt Synchronous Assignment with Async Fallback
        try {
            // Instantiate the job and call handle() directly for immediate assignment
            (new ProcessTicketCreation($ticket->id, $data['role_id'] ?? null))->handle();

            // Refresh ticket to get the staff_id assigned during handle()
            $ticket->refresh();
        } catch (\Throwable $e) {
            Log::warning("Sync ticket processing failed for Ticket #{$ticket->id}, falling back to queue.", [
                'error' => $e->getMessage()
            ]);

            ProcessTicketCreation::dispatch($ticket->id, $data['role_id'] ?? null);
        }

        return $ticket;
    }

    /**
     * Retrieve all tickets for a specific email with custom status priority.
     */
    public function getTicketsByEmail(string $email): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Ticket::whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->orderByRaw("FIELD(status, 'Open', 'Forwarded', 'Closed')")
            ->orderBy('date_created', 'desc')
            ->get();
    }

    /**
     * Get a simple list of users for selection in the view.
     */
    public function getSimpleUserList(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function updateTicketStatus(int $ticketId, int $userId, string $newStatus): \App\Models\Ticket
    {
        $ticket = Ticket::where('user_id', $userId)->findOrFail($ticketId);

        $ticket->update(['status' => $newStatus]);

        // Example of a side effect: 
        if ($newStatus === 'Closed') {
            $ticket->update(['date_closed' => now()]);
        }

        return $ticket;
    }

    /**
     * Update ticket content and synchronize attachments.
     */
    /**
     * Unified update method for both Guests and Admins.
     */
    public function updateTicket(Ticket $ticket, array $data, array $newFiles = [], ?User $performer = null): Ticket
    {
        try {
            return DB::transaction(function () use ($ticket, $data, $newFiles, $performer) {

                // 1. Handle File Synchronization (If files or deletions are provided)
                $currentAttachments = json_decode($ticket->attachments, true) ?? [];

                // Remove deleted files
                if (!empty($data['delete_attachments'])) {
                    $deleteList = json_decode($data['delete_attachments'], true) ?? [];
                    foreach ($deleteList as $path) {
                        if (in_array($path, $currentAttachments)) {
                            Storage::disk('public')->delete($path);
                            $currentAttachments = array_diff($currentAttachments, [$path]);
                        }
                    }
                }

                // Store new files
                foreach ($newFiles as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $currentAttachments[] = $file->storeAs('attachments', $filename, 'public');
                }

                // Update the attachments path in the data array
                $data['attachments'] = json_encode(array_values($currentAttachments));

                // 2. Business Logic: Handle "Closed" timestamp
                if (isset($data['status']) && $data['status'] === 'Closed' && !$ticket->date_closed) {
                    $data['date_closed'] = now();
                }

                // 3. Perform Update
                $ticket->update($data);

                // 4. Activity Logging (Create history for ALL updates)
                $ticket->routingHistories()->create([
                    'staff_id'  => $performer?->id,
                    'status'    => $ticket->status,
                    'routed_at' => now(),
                    'notes'     => $performer ? 'Admin/Staff updated ticket' : 'Guest/User updated ticket content',
                ]);

                return $ticket->load('role', 'staff');
            });
        } catch (\Throwable $e) {
            Log::error("Failed to update ticket [ID: {$ticket->id}]: " . $e->getMessage());
            throw $e;
        }
    }

}
