<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Mail\TicketResponseMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTicketResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ticketId;
    protected $message;
    protected $responderName;

    /**
     * Create a new job instance.
     */
    public function __construct($ticketId, $message, $responderName = null)
    {
        $this->ticketId = $ticketId;
        $this->message = $message;
        $this->responderName = $responderName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ticket = Ticket::find($this->ticketId);
        if (!$ticket) {
            Log::warning('SendTicketResponseJob: Ticket not found', ['ticket_id' => $this->ticketId]);
            return;
        }

        if (empty($ticket->email)) {
            Log::warning('SendTicketResponseJob: Ticket has no email address', ['ticket_id' => $this->ticketId]);
            return;
        }

        try {
            Mail::to($ticket->email)->send(
                new TicketResponseMail($ticket, $this->message, $this->responderName)
            );
            Log::info('SendTicketResponseJob: Email sent successfully', ['ticket_id' => $this->ticketId]);
        } catch (\Throwable $e) {
            Log::error('SendTicketResponseJob: Failed to send email', [
                'ticket_id' => $this->ticketId,
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-throw to mark job as failed
        }
    }
}