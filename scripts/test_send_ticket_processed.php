<?php
// One-off script to simulate a staff opening a ticket and trigger TicketProcessedMail
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketProcessedMail;
use Illuminate\Support\Facades\Log;

$ticketId = intval($argv[1] ?? 23);
echo "Running test for ticket id: {$ticketId}\n";

$ticket = Ticket::find($ticketId);
if (! $ticket) {
    echo "Ticket {$ticketId} not found.\n";
    exit(1);
}

$staff = User::whereHas('role')->first();
if (! $staff) {
    echo "No staff user found to simulate view.\n";
    exit(1);
}

try {
    if (empty($ticket->first_viewed_at)) {
        $ticket->first_viewed_at = now();
        $ticket->first_viewed_by = $staff->id;
        $ticket->save();
        echo "Recorded first_viewed_at for ticket {$ticketId} by staff {$staff->id}\n";
    } else {
        echo "Ticket already has first_viewed_at set: {$ticket->first_viewed_at}\n";
    }

    // Determine routing history first/current
    $histories = $ticket->routingHistories()->orderBy('routed_at','asc')->get();
    $firstAssignee = $histories->first() ? User::find($histories->first()->staff_id) : null;
    $lastAssignee = $histories->last() ? User::find($histories->last()->staff_id) : ($ticket->staff ? $ticket->staff : null);
    $isForwarded = ($firstAssignee && $lastAssignee && $firstAssignee->id !== $lastAssignee->id);

    echo "Sending TicketProcessedMail to {$ticket->email}...\n";
    Mail::to($ticket->email)->send(new TicketProcessedMail($ticket, $firstAssignee, $lastAssignee, $isForwarded));
    echo "Mail send attempted. Check logs and inbox/spam.\n";
} catch (Throwable $e) {
    echo "Exception sending mail: " . $e->getMessage() . "\n";
    Log::error('test_send_ticket_processed failed', ['error' => $e->getMessage(), 'ticket_id' => $ticketId]);
    exit(1);
}

exit(0);

