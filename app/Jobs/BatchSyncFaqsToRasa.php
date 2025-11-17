<?php

namespace App\Jobs;

use App\Models\FaqSyncQueue;
use App\Services\RasaBatchSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BatchSyncFaqsToRasa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $batchSize = 100
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RasaBatchSyncService $batchService): void
    {
        Log::info('Starting batch FAQ sync', [
            'batch_size' => $this->batchSize,
        ]);

        // Get pending syncs that haven't exceeded retry limit
        $pendingSyncs = FaqSyncQueue::with('faq')
            ->where('sync_status', 'pending')
            ->where('attempts', '<', 3)
            ->orderBy('created_at')
            ->limit($this->batchSize)
            ->get();

        if ($pendingSyncs->isEmpty()) {
            Log::info('No pending FAQs to sync');
            return;
        }

        Log::info('Found pending FAQs to sync', [
            'count' => $pendingSyncs->count(),
        ]);

        try {
            // Perform batch sync
            $results = $batchService->syncBatch($pendingSyncs);

            Log::info('Batch sync completed', [
                'total' => count($results),
                'successful' => collect($results)->where('success', true)->count(),
                'failed' => collect($results)->where('success', false)->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Batch sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Batch FAQ sync job failed permanently', [
            'error' => $exception->getMessage(),
        ]);
    }
}