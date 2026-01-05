<?php
// Script to send the Ticket Delivered (creation) email for a given ticket id
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketCreatedMail;

$ticketId = intval($argv[1] ?? 23);
echo "Sending Ticket Delivered email for ticket {$ticketId}\n";

$ticket = Ticket::find($ticketId);
if (! $ticket) { echo "Ticket not found\n"; exit(1); }

try {
    Mail::to($ticket->email)->send(new TicketCreatedMail($ticket));
    echo "Mail send attempted to {$ticket->email}\n";
} catch (Throwable $e) {
    echo "Failed to send: " . $e->getMessage() . "\n";
}

exit(0);

