<?php

namespace App\Console\Commands;

use App\Models\Faq;
use App\Models\FaqSyncQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileFaqSync extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'faq:reconcile-sync';

    /**
     * The console command description.
     */
    protected $description = 'Reconcile FAQ sync status and queue missing syncs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting FAQ sync reconciliation...');

        // Find FAQs that need syncing (hash mismatch or never synced)
        $faqsNeedingSync = Faq::whereRaw('sync_hash IS NULL OR sync_hash != ?', [
                DB::raw("SHA2(JSON_OBJECT('intent', intent, 'description', description, 'response', response, 'status', status, 'response_disabled', response_disabled), 256)")
            ])
            ->orWhereDoesntHave('syncQueue', function ($query) {
                $query->where('sync_status', 'synced');
            })
            ->get();

        if ($faqsNeedingSync->isEmpty()) {
            $this->info('All FAQs are in sync.');
            return Command::SUCCESS;
        }

        $this->info("Found {$faqsNeedingSync->count()} FAQs needing sync.");

        $queued = 0;
        foreach ($faqsNeedingSync as $faq) {
            // Check if already has a pending sync
            $hasPending = FaqSyncQueue::where('faq_id', $faq->id)
                ->where('sync_status', 'pending')
                ->exists();

            if (!$hasPending) {
                FaqSyncQueue::create([
                    'faq_id' => $faq->id,
                    'sync_type' => 'update',
                    'sync_status' => 'pending',
                ]);
                $queued++;
            }
        }

        $this->info("Queued {$queued} FAQs for sync.");
        $this->info('Reconciliation complete.');

        return Command::SUCCESS;
    }
}