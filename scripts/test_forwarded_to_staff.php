<?php
// Quick test script: send a TicketAssignedMail (forwarded) to the first user with an email
// Usage: php scripts/test_forwarded_to_staff.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Ticket;
use App\Mail\TicketAssignedMail;
use Illuminate\Support\Facades\Mail;

echo "Looking up a staff user with an email...\n";
$staff = User::whereNotNull('email')->where('email', '<>', '')->first();
if (!$staff) {
    echo "No user with an email address found. Create a user first.\n";
    exit(1);
}

// Build a non-persisted dummy ticket for rendering
$ticket = new Ticket([
    'id' => rand(9000, 9999),
    'question' => 'Test forwarded ticket — please review and respond',
    'category' => 'Testing',
    'email' => $staff->email,
    'date_created' => now(),
]);

try {
    Mail::to($staff->email)->send(new TicketAssignedMail($ticket, 'forwarded'));
    echo "Mail send attempted to: {$staff->email}\n";
} catch (Throwable $e) {
    echo "Mail send failed: " . $e->getMessage() . "\n";
    echo "Check storage/logs/laravel.log for details.\n";
    exit(1);
}

echo "Done. Check the recipient inbox (or mail logs).\n";

