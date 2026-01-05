<?php

// Bootstrap Laravel without interactive tinker; create a test ticket and print it as JSON.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Generate unique values
$ts = time();
$email = 'tinker' . $ts . '@example.com';
$recepient = 'tinker-' . $ts;

// Insert a ticket directly
$id = DB::table('tickets')->insertGetId([
    'category_id' => null,
    'question' => 'Tinker-created test ticket',
    'response' => null,
    'recepient_id' => $recepient,
    'email' => $email,
    'status' => 'Open',
    'staff_id' => null,
    'date_created' => now(),
    'date_closed' => null,
    'attachments' => json_encode([]),
    'created_at' => now(),
    'updated_at' => now(),
]);

$row = DB::table('tickets')->where('id', $id)->first();

echo json_encode([
    'inserted_id' => $id,
    'ticket' => (array) $row,
]);

