<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$identifier = $argv[1] ?? null;
if (!$identifier) {
    echo json_encode(['error' => 'Please provide an email or recepient id as the first argument']);
    exit(1);
}

$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
$resolvedEmail = null;
if (!$isEmail) {
    $resolvedEmail = DB::table('tickets')->where('recepient_id', $identifier)->orderBy('date_created', 'desc')->value('email');
    if (!($resolvedEmail && filter_var($resolvedEmail, FILTER_VALIDATE_EMAIL))) {
        $resolvedEmail = null;
    }
}

$query = DB::table('tickets');
if ($isEmail) {
    $query->whereRaw('LOWER(email) = ?', [strtolower($identifier)]);
} else {
    if ($resolvedEmail) {
        $query->where(function ($q) use ($identifier, $resolvedEmail) {
            $q->where('recepient_id', $identifier)
              ->orWhereRaw('LOWER(email) = ?', [strtolower($resolvedEmail)]);
        });
    } else {
        $query->where('recepient_id', $identifier);
    }
}

$tickets = $query->orderByRaw("FIELD(status, 'Open', 'Forwarded', 'Closed')")->orderBy('date_created', 'desc')->get();

echo json_encode(['count' => $tickets->count(), 'tickets' => $tickets->toArray()], JSON_PRETTY_PRINT);

