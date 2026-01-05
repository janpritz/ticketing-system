<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketProcessedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $firstAssignee;
    public $currentAssignee;
    public $isForwarded;

    /**
     * Create a new message instance.
     */
    public function __construct($ticket, $firstAssignee = null, $currentAssignee = null, $isForwarded = false)
    {
        $this->ticket = $ticket;
        $this->firstAssignee = $firstAssignee;
        $this->currentAssignee = $currentAssignee;
        $this->isForwarded = $isForwarded;
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

        return $this->subject($subject)
                    ->view('emails.ticket_processed')
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
            $rows = \App\Models\TicketRoutingHistory::where('ticket_id', $ticket->id)
                        ->orderBy('routed_at','asc')
                        ->pluck('staff_id')
                        ->filter()
                        ->toArray();

            if (!empty($rows)) {
                $names = [];
                $users = \App\Models\User::whereIn('id', $rows)->get()->keyBy('id');
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
            \Illuminate\Support\Facades\Log::error('Failed to build forward history: ' . $e->getMessage(), ['ticket_id' => $ticket->id]);
            $forwardHistory = null;
        }

        return [
            'ticketNo' => $ticketNo,
            'ticket' => $ticket,
            'firstAssignee' => $this->firstAssignee,
            'currentAssignee' => $this->currentAssignee,
            'isForwarded' => $this->isForwarded,
            'forwardHistory' => $forwardHistory,
        ];
    }
}

