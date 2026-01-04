<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTicketCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ticketId;
    protected $categoryId;

    /**
     * Create a new job instance.
     */
    public function __construct($ticketId, $categoryId)
    {
        $this->ticketId = $ticketId;
        $this->categoryId = $categoryId;
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
            'category_id' => $this->categoryId
        ]);

        // Determine role based on the selected category id
        $roleModel = null;
        $categoryModel = null;

        if ($this->categoryId) {
            $categoryModel = Category::with('role')->find($this->categoryId);
        }

        // If category not provided or not found, clear category and staff on ticket and exit
        if (!$categoryModel) {
            $ticket->update(['category_id' => null, 'staff_id' => null]);
            Log::info('ProcessTicketCreation: Category not found or not provided; cleared category and staff', [
                'ticket_id' => $this->ticketId,
                'category_id' => $this->categoryId
            ]);
            return;
        }

        // Use the category's role if available
        $roleModel = $categoryModel->role;
        if (!$roleModel) {
            // Category exists but has no associated role; do not assign staff
            $ticket->update(['staff_id' => null]);
            Log::info('ProcessTicketCreation: Category has no role; not assigning staff', [
                'ticket_id' => $this->ticketId,
                'category_id' => $categoryModel->id
            ]);
            return;
        }

        // Find staff with the lowest open-ticket load within the selected role
        $staff = null;
        if ($roleModel) {
            Log::info('ProcessTicketCreation: Searching for staff in role', [
                'role_id' => $roleModel->id,
                'role_name' => $roleModel->name
            ]);
            
            $candidates = User::where('role_id', $roleModel->id)
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
            'category_id' => $ticket->category_id
        ]);

        // Record initial routing history
        TicketRoutingHistory::create([
            'ticket_id' => $ticket->id,
            'staff_id' => $staffId,
            'status' => 'Open',
            'routed_at' => now(),
            'notes' => 'Ticket created' . ($staffId ? ' and assigned to staff ' . $staffId : ''),
        ]);

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
        }
    }
}
