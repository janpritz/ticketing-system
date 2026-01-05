<?php
// Script to simulate forwarding a ticket through N staff users and send the forwarded notification
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketProcessedMail;

$ticketId = intval($argv[1] ?? 23);
$count = intval($argv[2] ?? 5);

echo "Simulating forward chain of {$count} for ticket {$ticketId}\n";

$ticket = Ticket::find($ticketId);
if (! $ticket) { echo "Ticket not found\n"; exit(1); }

$staffUsers = User::whereHas('role')->take($count)->get();
if ($staffUsers->count() < 1) { echo "No staff users available\n"; exit(1); }

// Delete existing routing history for test (note: do not do this in production)
TicketRoutingHistory::where('ticket_id', $ticket->id)->delete();

$ts = now()->subMinutes($count + 1);
foreach ($staffUsers as $u) {
    TicketRoutingHistory::create([
        'ticket_id' => $ticket->id,
        'staff_id' => $u->id,
        'status' => 'Forwarded',
        'routed_at' => $ts->toDateTimeString(),
        'notes' => 'Test forward to ' . $u->name,
    ]);
    $ts->addMinute();
}

$last = $staffUsers->last();
$ticket->staff_id = $last->id;
$ticket->status = 'Forwarded';
$ticket->save();

// Send forwarded notification
Mail::to($ticket->email)->send(new TicketProcessedMail($ticket, null, $last, true));

echo "Forwarded notification attempted to {$ticket->email}\n";

exit(0);

