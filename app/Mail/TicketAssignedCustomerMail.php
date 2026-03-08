<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketAssignedCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $staff;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, User $staff)
    {
        $this->ticket = $ticket;
        $this->staff = $staff;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $createdAt = $this->ticket->date_created ?: $this->ticket->created_at;
        $year = $createdAt ? date('Y', strtotime($createdAt)) : date('Y');
        $ticketNo = sprintf('T-%s-%06d', $year, $this->ticket->id);
        $subject = 'Ticket Assigned: ' . $ticketNo;

        $categoryName = $this->ticket->role ? $this->ticket->role->name : 'Uncategorized';
        $staffName = $this->staff->name;
        $createdAt = $this->ticket->date_created ?: $this->ticket->created_at;

        return $this->subject($subject)
                    ->view('emails.ticket_assigned_customer')
                    ->with([
                        'ticketNo' => $ticketNo,
                        'ticket' => $this->ticket,
                        'staff' => $this->staff,
                        'categoryName' => $categoryName,
                        'staffName' => $staffName,
                        'createdAt' => $createdAt,
                    ]);
    }
}
