<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;
    public $action; // 'assigned' or 'forwarded'

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, string $action = 'assigned')
    {
        $this->ticket = $ticket;
        $this->action = $action;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $ticketNo = sprintf('T-%s-%06d', date('Y'), $this->ticket->id ?? 0);
        $subject = $this->action === 'forwarded' ? "Ticket Forwarded: {$ticketNo}" : "Ticket Assigned: {$ticketNo}";

        return $this->subject($subject)
            ->view('emails.ticket_assigned')
            ->with([
                'ticket' => $this->ticket,
                'ticketNo' => $ticketNo,
                'action' => $this->action,
                'dashboardUrl' => url('/staff/dashboard?ticket_id=' . ($this->ticket->id ?? '')),
            ]);
    }
}

