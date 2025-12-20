<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public string $messageBody;
    public ?string $responderName;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, string $messageBody, ?string $responderName = null)
    {
        $this->ticket = $ticket;
        $this->messageBody = $messageBody;
        $this->responderName = $responderName;
        
        // Eager load staff relationship to avoid N+1 queries
        $this->ticket->load('staff');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $createdAt = $this->ticket->date_created ?: $this->ticket->created_at;
        $year = $createdAt ? date('Y', strtotime($createdAt)) : date('Y');
        $ticketNo = sprintf('T-%s-%04d', $year, $this->ticket->id);

        return $this
            ->subject('Re: Ticket ' . $ticketNo)
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Sangkay Ticketing System'))
            ->view('emails.ticket_response')
            ->with([
                'ticketNo' => $ticketNo,
                'ticket' => $this->ticket,
                'messageBody' => $this->messageBody,
                'responderName' => $this->responderName,
                'staffEmail' => $this->ticket->staff?->email,
                'staffName' => $this->ticket->staff?->name ?: 'Support Team',
            ]);
    }
}