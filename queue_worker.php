<?php

// Queue Worker Script for Cron
// This script runs the Laravel queue worker to process queued jobs

// Set the path to your Laravel application directory
$laravelPath = __DIR__;

// Change to the Laravel directory
chdir($laravelPath);

// Execute the queue work command
// Run for a limited time to prevent hanging
exec('php artisan queue:work --stop-when-empty --max-jobs=50 --max-time=300 2>&1', $output, $returnCode);

// Log the output for debugging
$logFile = __DIR__ . '/storage/logs/queue_worker.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Queue worker executed\n", FILE_APPEND);
file_put_contents($logFile, "Return code: $returnCode\n", FILE_APPEND);
file_put_contents($logFile, "Output:\n" . implode("\n", $output) . "\n\n", FILE_APPEND);

?>