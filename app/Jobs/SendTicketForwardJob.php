<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PushService;

class SendTicketForwardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ticketId;
    protected $newStaffId;
    protected $forwardedByUserId;
    protected $notes;

    /**
     * Create a new job instance.
     */
    public function __construct($ticketId, $newStaffId, $forwardedByUserId = null, $notes = null)
    {
        $this->ticketId = $ticketId;
        $this->newStaffId = $newStaffId;
        $this->forwardedByUserId = $forwardedByUserId;
        $this->notes = $notes;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ticket = Ticket::find($this->ticketId);
        if (!$ticket) {
            Log::warning('SendTicketForwardJob: Ticket not found', ['ticket_id' => $this->ticketId]);
            return;
        }

        $newStaff = User::find($this->newStaffId);
        if (!$newStaff) {
            Log::warning('SendTicketForwardJob: New staff member not found', [
                'ticket_id' => $this->ticketId,
                'staff_id' => $this->newStaffId
            ]);
            return;
        }

        Log::info('SendTicketForwardJob: Sending push notification for forwarded ticket', [
            'ticket_id' => $this->ticketId,
            'to_staff' => $this->newStaffId,
            'forwarded_by' => $this->forwardedByUserId
        ]);

        // Send push notification to the new staff member
        $subscriptionPath = 'push_subscriptions/user-' . $this->newStaffId . '.json';

        if (Storage::exists($subscriptionPath)) {
            try {
                $ticketsUrl = url('/staff/dashboard?ticket_id=' . $ticket->id);
                $payload = [
                    'title' => 'Ticket Forwarded to You',
                    'body' => "Ticket #{$ticket->id} has been forwarded to you for handling.",
                    'url' => $ticketsUrl,
                    'data' => [
                        'url' => $ticketsUrl,
                        'type' => 'ticket_forwarded',
                        'ticket_id' => $ticket->id
                    ],
                    'icon' => '/favicon.ico',
                    'badge' => '/favicon.ico'
                ];

                $pushService = app(PushService::class);
                $results = $pushService->sendToUser($this->newStaffId, $payload);

                if (empty($results)) {
                    Log::info("SendTicketForwardJob: No push subscription found for staff {$newStaff->name} (ID: {$this->newStaffId})");
                } else {
                    Log::info("SendTicketForwardJob: Push notification sent to {$newStaff->name} for forwarded ticket {$ticket->id}");

                    foreach ($results as $report) {
                        if (is_array($report)) {
                            if (isset($report['success'])) {
                                if (!$report['success']) {
                                    Log::warning("SendTicketForwardJob: Push failed for staff {$newStaff->name} - " . ($report['reason'] ?? 'unknown reason'));
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error("SendTicketForwardJob: Failed to send push notification to staff {$newStaff->name}: " . $e->getMessage());
            }
        } else {
            Log::info("SendTicketForwardJob: Push subscription file not found for staff {$newStaff->name} (ID: {$this->newStaffId})");
        }
    }
}