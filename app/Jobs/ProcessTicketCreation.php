<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Role;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTicketCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ticketId;
    protected $roleId;

    /**
     * Create a new job instance.
     */
    public function __construct($ticketId, $roleId)
    {
        $this->ticketId = $ticketId;
        $this->roleId = $roleId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ticket = Ticket::find($this->ticketId);
        if (!$ticket) {
            Log::error('ProcessTicketCreation: Ticket not found', ['ticket_id' => $this->ticketId]);
            return;
        }

        Log::info('ProcessTicketCreation: Starting ticket assignment', [
            'ticket_id' => $this->ticketId,
            'role_id' => $this->roleId
        ]);

        // Determine role based on the selected role id
        $roleModel = null;

        if ($this->roleId) {
            $roleModel = Role::find($this->roleId);
        }

        // If role not provided or not found, clear role and staff on ticket and exit
        if (!$roleModel) {
            $ticket->update(['role_id' => null, 'staff_id' => null]);
            Log::info('ProcessTicketCreation: Role not found or not provided; cleared role and staff', [
                'ticket_id' => $this->ticketId,
                'role_id' => $this->roleId
            ]);
            return;
        }

        // Update ticket with role_id
        $ticket->update(['role_id' => $roleModel->id]);

        // Find staff with the lowest open-ticket load within the selected role
        $staff = null;
        if ($roleModel) {
            Log::info('ProcessTicketCreation: Searching for staff in role', [
                'role_id' => $roleModel->id,
                'role_name' => $roleModel->name
            ]);
            
            // Find users who have this role assigned via user_roles table
            $candidates = User::whereHas('roles', function($q) use ($roleModel) {
                    $q->where('roles.id', $roleModel->id);
                })
                ->withCount(['assignedTickets as open_tickets_count' => function ($q) {
                    $q->where('status', 'Open');
                }])
                ->get();

            Log::info('ProcessTicketCreation: Found candidates', [
                'role_id' => $roleModel->id,
                'candidate_count' => $candidates->count(),
                'candidates' => $candidates->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'open_tickets' => $user->open_tickets_count ?? 0
                    ];
                })
            ]);

            if ($candidates->isNotEmpty()) {
                $min = $candidates->min('open_tickets_count');
                $ties = $candidates->where('open_tickets_count', $min);
                
                Log::info('ProcessTicketCreation: Load balancing result', [
                    'min_open_tickets' => $min,
                    'tie_count' => $ties->count()
                ]);
                
                $staff = $ties->count() > 1 ? $ties->random() : $ties->first();
                
                Log::info('ProcessTicketCreation: Selected staff', [
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->name,
                    'open_tickets' => $staff->open_tickets_count ?? 0
                ]);
            } else {
                Log::warning('ProcessTicketCreation: No staff found for role', [
                    'role_id' => $roleModel->id,
                    'role_name' => $roleModel->name
                ]);
            }
        } else {
            Log::warning('ProcessTicketCreation: No role model available for assignment');
        }

        // Update ticket with staff_id
        $staffId = $staff ? $staff->id : null;
        $ticket->update(['staff_id' => $staffId]);

        Log::info('ProcessTicketCreation: Updated ticket assignment', [
            'ticket_id' => $this->ticketId,
            'staff_id' => $staffId,
            'role_id' => $ticket->role_id
        ]);

        // Record initial routing history
        TicketRoutingHistory::create([
            'ticket_id' => $ticket->id,
            'staff_id' => $staffId,
            'status' => 'Open',
            'routed_at' => now(),
            'notes' => 'Ticket created' . ($staffId ? ' and assigned to staff ' . $staffId : ''),
        ]);

        // Send confirmation email to ticket creator
        try {
            if ($ticket->email) {
                if ($staff) {
                    // Ticket created and assigned to staff - send assigned email
                    Mail::to($ticket->email)->send(new \App\Mail\TicketAssignedCustomerMail($ticket, $staff));
                    Log::info('ProcessTicketCreation: Sent ticket assigned email to customer', ['ticket_id' => $ticket->id, 'email' => $ticket->email, 'staff_id' => $staff->id]);
                } else {
                    // Ticket created but not assigned - send created email
                    Mail::to($ticket->email)->send(new \App\Mail\TicketCreatedMail($ticket));
                    Log::info('ProcessTicketCreation: Sent ticket created email', ['ticket_id' => $ticket->id, 'email' => $ticket->email]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ProcessTicketCreation: Failed to send customer email: ' . $e->getMessage(), ['ticket_id' => $ticket->id]);
        }

        // Send push notification to the assigned staff
        if ($ticket->staff_id) {
            $subscriptionPath = 'push_subscriptions/user-' . $ticket->staff_id . '.json';
            if (Storage::exists($subscriptionPath)) {
                try {
                    $ticketUrl = url('/staff/dashboard?ticket_id=' . $ticket->id);
                    $payload = [
                        'title' => 'You have received a new ticket',
                        'body' => $ticket->question,
                        'url' => $ticketUrl,
                        'ticket_id' => $ticket->id,
                        'data' => [
                            'url' => $ticketUrl,
                            'ticket_id' => $ticket->id
                        ],
                    ];

                    $pushService = app(\App\Services\PushService::class);
                    $results = $pushService->sendToUser($ticket->staff_id, $payload);

                    if (empty($results)) {
                        Log::info('PushService: no subscription found for user ' . $ticket->staff_id . ' when assigning ticket ' . $ticket->id);
                    } else {
                        foreach ($results as $report) {
                            if (is_array($report)) {
                                if (isset($report['success'])) {
                                    if (!$report['success']) {
                                        Log::warning('PushService: push failed for user ' . $ticket->staff_id . ' endpoint=' . ($report['endpoint'] ?? 'unknown') . ' reason=' . ($report['reason'] ?? 'unknown') . ' ticket=' . $ticket->id);
                                    } else {
                                        Log::info('PushService: push succeeded for user ' . $ticket->staff_id . ' endpoint=' . ($report['endpoint'] ?? 'unknown') . ' ticket=' . $ticket->id);
                                    }
                                } else {
                                    foreach ($report as $r) {
                                        if (isset($r['success']) && !$r['success']) {
                                            Log::warning('PushService: push failed for user ' . $ticket->staff_id . ' endpoint=' . ($r['endpoint'] ?? 'unknown') . ' reason=' . ($r['reason'] ?? 'unknown') . ' ticket=' . $ticket->id);
                                        } elseif (isset($r['success'])) {
                                            Log::info('PushService: push succeeded for user ' . $ticket->staff_id . ' endpoint=' . ($r['endpoint'] ?? 'unknown') . ' ticket=' . $ticket->id);
                                        }
                                    }
                                }
                            } else {
                                Log::info('PushService: push report for user ' . $ticket->staff_id . ' ticket=' . $ticket->id . ' report=' . json_encode($report));
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Push send failed for ticket assignment (exception): ' . $e->getMessage() . ' ticket=' . $ticket->id . ' staff=' . $ticket->staff_id);
                }
            } else {
                Log::info('PushService: subscription file not found for user ' . $ticket->staff_id . '; skipping push for ticket ' . $ticket->id);
            }
            // Also send an email notification to the assigned staff if they allow email notifications
            try {
                $staffUser = User::find($ticket->staff_id);
                if ($staffUser && !empty($staffUser->email) && $staffUser->getAttribute('email_notifications')) {
                    Mail::to($staffUser->email)->send(new \App\Mail\TicketAssignedMail($ticket, 'assigned'));
                    Log::info('ProcessTicketCreation: Sent ticket-assigned email to staff', ['ticket_id' => $ticket->id, 'staff_id' => $staffUser->id, 'email' => $staffUser->email]);
                }
            } catch (\Throwable $e) {
                Log::warning('ProcessTicketCreation: Failed to send ticket-assigned email to staff: ' . $e->getMessage(), ['ticket_id' => $ticket->id]);
            }
        }
    }
}
