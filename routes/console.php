<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketResponseMail;
use App\Models\Ticket;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email?}', function ($email = null) {
    $to = $email ?: (config('mail.from.address') ?: 'test@example.com');

    $ticket = new Ticket([
        'id' => 9999,
        'category' => 'Diagnostics',
        'question' => 'This is a test message to verify email delivery.',
        'recepient_id' => '0',
        'email' => $to,
        'status' => 'Open',
        'date_created' => now(),
    ]);

    try {
        Mail::to($to)->send(
            new TicketResponseMail($ticket, 'SMTP test from Sangkay Ticketing System.', 'Mail Tester')
        );
        $this->info('Test email sent to: ' . $to);
    } catch (\Throwable $e) {
        $this->error('Failed to send: ' . $e->getMessage());
    }
})->purpose('Send a test email using configured mailer');
