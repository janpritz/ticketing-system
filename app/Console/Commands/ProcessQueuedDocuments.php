<?php

namespace App\Console\Commands;

use App\Jobs\ProcessQueuedDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ProcessQueuedDocuments extends Command
{
    protected $signature = 'queue:process-uploads';
    protected $description = 'Process queued document uploads to Rasa server';

    public function handle()
    {
        $queuedDir = storage_path('app/queued_documents');

        if (!file_exists($queuedDir)) {
            $this->info('No queued documents directory found');
            return;
        }

        $files = glob($queuedDir . '/*.txt');
        $this->info("Found " . count($files) . " files in {$queuedDir}");
        foreach ($files as $file) {
            $this->info("File: {$file}");
        }

        $pendingCount = count($files);

        // Check for retry files (files with next_retry_at in the past)
        $retryCount = 0;
        foreach ($files as $file) {
            $fileData = json_decode(file_get_contents($file), true);
            if ($fileData && isset($fileData['next_retry_at'])) {
                $retryTime = strtotime($fileData['next_retry_at']);
                if ($retryTime && $retryTime <= time()) {
                    $retryCount++;
                } else {
                    // Remove from pending count if not ready for retry
                    $pendingCount--;
                }
            }
        }

        $this->info("Found {$pendingCount} pending and {$retryCount} retry documents to process");

        // Process all valid files
        foreach ($files as $file) {
            $fileName = basename($file);
            $this->info("Dispatching job for file: {$fileName}");
            $fileData = json_decode(file_get_contents($file), true);
            if (!$fileData) {
                $this->error("Invalid JSON in file: {$file}");
                continue;
            }

            // Check if it's ready for retry
            if (isset($fileData['next_retry_at'])) {
                $retryTime = strtotime($fileData['next_retry_at']);
                if ($retryTime && $retryTime > time()) {
                    continue; // Not ready for retry yet
                }
            }

            ProcessQueuedDocument::dispatch($fileName);
        }

        $this->info('Queued document processing jobs dispatched');
    }
}
