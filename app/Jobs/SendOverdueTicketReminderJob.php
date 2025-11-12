<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PushService;

class SendOverdueTicketReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $overdueDays = env('TICKET_OVERDUE_DAYS', 1);
        $cutoffDate = now()->subDays($overdueDays);

        Log::info("SendOverdueTicketReminderJob: Checking for tickets older than {$overdueDays} days (before {$cutoffDate})");

        // Find overdue tickets that are still open and assigned to staff
        $overdueTickets = Ticket::where('status', 'Open')
            ->whereNotNull('staff_id')
            ->where('date_created', '<', $cutoffDate)
            ->with('staff')
            ->get();

        if ($overdueTickets->isEmpty()) {
            Log::info('SendOverdueTicketReminderJob: No overdue tickets found');
            return;
        }

        // Group tickets by staff member
        $ticketsByStaff = $overdueTickets->groupBy('staff_id');

        $pushService = app(PushService::class);
        $totalRemindersSent = 0;

        foreach ($ticketsByStaff as $staffId => $tickets) {
            $staff = $tickets->first()->staff;

            if (!$staff) {
                Log::warning("SendOverdueTicketReminderJob: Staff member {$staffId} not found");
                continue;
            }

            $ticketCount = $tickets->count();

            // Create reminder message
            $message = $ticketCount === 1
                ? "You have 1 overdue ticket that needs attention."
                : "You have {$ticketCount} overdue tickets that need attention.";

            // Send push notification
            $subscriptionPath = 'push_subscriptions/user-' . $staffId . '.json';

            if (Storage::exists($subscriptionPath)) {
                try {
                    $ticketsUrl = url('/staff/tickets');
                    $payload = [
                        'title' => 'Overdue Tickets Reminder',
                        'body' => $message,
                        'url' => $ticketsUrl,
                        'data' => [
                            'url' => $ticketsUrl,
                            'type' => 'overdue_reminder',
                            'ticket_count' => $ticketCount
                        ],
                        'icon' => '/favicon.ico',
                        'badge' => '/favicon.ico'
                    ];

                    $results = $pushService->sendToUser($staffId, $payload);

                    if (empty($results)) {
                        Log::info("SendOverdueTicketReminderJob: No push subscription found for staff {$staff->name} (ID: {$staffId})");
                    } else {
                        $totalRemindersSent++;
                        Log::info("SendOverdueTicketReminderJob: Sent overdue reminder to {$staff->name} for {$ticketCount} tickets");

                        foreach ($results as $report) {
                            if (is_array($report)) {
                                if (isset($report['success'])) {
                                    if (!$report['success']) {
                                        Log::warning("SendOverdueTicketReminderJob: Push failed for staff {$staff->name} - " . ($report['reason'] ?? 'unknown reason'));
                                    }
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error("SendOverdueTicketReminderJob: Failed to send push notification to staff {$staff->name}: " . $e->getMessage());
                }
            } else {
                Log::info("SendOverdueTicketReminderJob: Push subscription file not found for staff {$staff->name} (ID: {$staffId})");
            }
        }

        Log::info("SendOverdueTicketReminderJob: Completed. Sent {$totalRemindersSent} reminders to " . $ticketsByStaff->count() . " staff members.");
    }
}