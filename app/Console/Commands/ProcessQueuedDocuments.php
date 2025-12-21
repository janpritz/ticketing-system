<?php

namespace App\Console\Commands;

use App\Jobs\ProcessQueuedDocument;
use App\Models\QueuedDocument;
use Illuminate\Console\Command;

class ProcessQueuedDocuments extends Command
{
    protected $signature = 'queue:process-uploads';
    protected $description = 'Process queued document uploads to Rasa server';

    public function handle()
    {
        $pending = QueuedDocument::pending()->count();
        $failed = QueuedDocument::readyForRetry()->count();

        $this->info("Found {$pending} pending and {$failed} failed documents to process");

        // Process pending documents
        QueuedDocument::pending()->each(function ($doc) {
            ProcessQueuedDocument::dispatch($doc);
        });

        // Process failed documents ready for retry
        QueuedDocument::readyForRetry()->each(function ($doc) {
            ProcessQueuedDocument::dispatch($doc);
        });

        $this->info('Queued document processing jobs dispatched');
    }
}
