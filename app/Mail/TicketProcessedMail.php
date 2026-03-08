<?php

namespace App\Mail;

use App\Models\{User, TicketRoutingHistory};
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TicketProcessedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $firstAssignee;
    public $currentAssignee;
    public $isForwarded;

    public $seenByStaff;

    /**
     * Create a new message instance.
     */
    public function __construct($ticket, $firstAssignee = null, $currentAssignee = null, $isForwarded = false, $seenByStaff = null)
    {
        $this->ticket = $ticket;
        $this->firstAssignee = $firstAssignee;
        $this->currentAssignee = $currentAssignee;
        $this->isForwarded = $isForwarded;
        $this->seenByStaff = $seenByStaff;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $createdAt = $this->ticket->date_created ?: $this->ticket->created_at;
        $year = $createdAt ? date('Y', strtotime($createdAt)) : date('Y');
        $ticketNo = sprintf('T-%s-%06d', $year, $this->ticket->id);
        $subject = $this->isForwarded ? 'Ticket Forwarded: ' . $ticketNo : 'Ticket Seen: ' . $ticketNo;
        $view = $this->isForwarded ? 'emails.ticket_forwarded' : 'emails.ticket_processed';

        return $this->subject($subject)
                    ->view($view)
                    ->with($this->prepareViewData($ticketNo));
    }

    /**
     * Build view data payload including forward history string.
     */
    protected function prepareViewData(string $ticketNo): array
    {
        $ticket = $this->ticket;

        // Build forward history (names in routed_at asc order)
        $forwardHistory = null;
        try {
            $rows = TicketRoutingHistory::where('ticket_id', $ticket->id)
                        ->orderBy('routed_at','asc')
                        ->pluck('staff_id')
                        ->filter()
                        ->toArray();

            // Treat as a forward only when there are at least 2 routing entries
            if (count($rows) > 1) {
                $names = [];
                $users = User::whereIn('id', $rows)->get()->keyBy('id');
                foreach ($rows as $sid) {
                    if (isset($users[$sid])) {
                        $names[] = $users[$sid]->name;
                    }
                }
                if (!empty($names)) {
                    $forwardHistory = implode(' -> ', $names);
                }
            }
        } catch (\Throwable $e) {
            // If anything goes wrong, log and continue without forward history
            Log::error('Failed to build forward history: ' . $e->getMessage(), ['ticket_id' => $ticket->id]);
            $forwardHistory = null;
        }

        $categoryName = $ticket->role ? $ticket->role->name : 'Uncategorized';
        $staffName = $this->currentAssignee ? $this->currentAssignee->name : 'Unassigned';
        $createdAt = $ticket->date_created ?: $ticket->created_at;

        return [
            'ticketNo' => $ticketNo,
            'ticket' => $ticket,
            'firstAssignee' => $this->firstAssignee,
            'currentAssignee' => $this->currentAssignee,
            'isForwarded' => $this->isForwarded,
            'forwardHistory' => $forwardHistory,
            'categoryName' => $categoryName,
            'staffName' => $staffName,
            'createdAt' => $createdAt,
            'seenByStaff' => $this->seenByStaff,
        ];
    }
}

