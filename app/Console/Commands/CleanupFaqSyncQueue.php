<?php

namespace App\Console\Commands;

use App\Models\FaqSyncQueue;
use Illuminate\Console\Command;

class CleanupFaqSyncQueue extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'faq:cleanup-sync-queue {--days=30 : Number of days to keep synced records}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old synced FAQ sync queue entries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("Cleaning up sync queue entries older than {$days} days...");

        $deleted = FaqSyncQueue::where('sync_status', 'synced')
            ->where('synced_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} old sync queue entries.");

        return Command::SUCCESS;
    }
}