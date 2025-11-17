<?php

namespace App\Console\Commands;

use App\Jobs\BatchSyncFaqsToRasa;
use Illuminate\Console\Command;

class SyncPendingFaqs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'faq:sync-pending {--batch-size=100 : Number of FAQs to sync per batch}';

    /**
     * The console command description.
     */
    protected $description = 'Sync pending FAQs to Rasa server';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $batchSize = (int) $this->option('batch-size');

        $this->info("Dispatching batch sync job (batch size: {$batchSize})...");

        BatchSyncFaqsToRasa::dispatch($batchSize);

        $this->info('Batch sync job dispatched successfully.');

        return Command::SUCCESS;
    }
}