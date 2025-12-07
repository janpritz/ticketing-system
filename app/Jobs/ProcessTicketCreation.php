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
    protected $category;

    /**
     * Create a new job instance.
     */
    public function __construct($ticketId, $category)
    {
        $this->ticketId = $ticketId;
        $this->category = $category;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ticket = Ticket::find($this->ticketId);
        if (!$ticket) {
            return;
        }

        // Determine role based on the selected category
        $roleModel = null;
        $categoryModel = Category::where('name', $this->category)->with('role')->first();
        if ($categoryModel && $categoryModel->role) {
            $roleModel = $categoryModel->role;
        } else {
            $primaryRole = Role::where('name', 'Primary Administrator')->first();
            if ($primaryRole) {
                $categoryModel = Category::firstOrCreate(
                    ['name' => $this->category, 'role_id' => $primaryRole->id],
                    ['description' => null]
                );
            }
            $roleModel = ($categoryModel && $categoryModel->role) ? $categoryModel->role : $primaryRole;
        }

        // Find staff with the lowest open-ticket load within the selected role
        $staff = null;
        if ($roleModel) {
            $candidates = User::where('role_id', $roleModel->id)
                ->withCount(['assignedTickets as open_tickets_count' => function ($q) {
                    $q->where('status', 'Open');
                }])
                ->get();

            if ($candidates->isNotEmpty()) {
                $min = $candidates->min('open_tickets_count');
                $ties = $candidates->where('open_tickets_count', $min);
                $staff = $ties->count() ? $ties->random() : $ties->first();
            }
        }

        // Update ticket with staff_id
        $ticket->update(['staff_id' => $staff ? $staff->id : null]);

        // Record initial routing history
        TicketRoutingHistory::create([
            'ticket_id' => $ticket->id,
            'staff_id' => $ticket->staff_id,
            'status' => 'Open',
            'routed_at' => now(),
            'notes' => 'Ticket created' . ($ticket->staff_id ? ' and assigned' : ''),
        ]);

        // Send push notification to the assigned staff
        if ($ticket->staff_id) {
            $subscriptionPath = 'push_subscriptions/user-' . $ticket->staff_id . '.json';
            if (Storage::exists($subscriptionPath)) {
                try {
                    $ticketUrl = url('/tickets/' . $ticket->id);
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
