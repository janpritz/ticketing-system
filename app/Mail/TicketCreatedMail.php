<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
        // Eager load staff and category to include in the confirmation email
        $this->ticket->load('staff', 'category');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $createdAt = $this->ticket->date_created ?: $this->ticket->created_at;
        $year = $createdAt ? date('Y', strtotime($createdAt)) : date('Y');
        $ticketNo = sprintf('T-%s-%06d', $year, $this->ticket->id);

        $categoryName = $this->ticket->category?->name ?: 'Uncategorized';

        return $this
            ->subject('Ticket Delivered: ' . $ticketNo)
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Sangkay Ticketing System'))
            ->view('emails.ticket_created')
            ->with([
                'ticketNo' => $ticketNo,
                'ticket' => $this->ticket,
                'staffName' => $this->ticket->staff?->name ?: 'Unassigned',
                'createdAt' => $createdAt,
                'categoryName' => $categoryName,
            ]);
    }
}

