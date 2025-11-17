<?php

namespace App\Jobs;

use App\Models\Faq;
use App\Models\FaqSyncQueue;
use App\Services\FaqSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncFaqToRasa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $faqId,
        public string $syncType,
        public int $syncQueueId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FaqSyncService $syncService): void
    {
        $syncQueue = FaqSyncQueue::find($this->syncQueueId);
        
        if (!$syncQueue) {
            Log::warning('Sync queue entry not found', [
                'sync_queue_id' => $this->syncQueueId,
                'faq_id' => $this->faqId,
            ]);
            return;
        }

        // Skip if already synced
        if ($syncQueue->sync_status === 'synced') {
            Log::info('FAQ already synced, skipping', [
                'faq_id' => $this->faqId,
                'sync_queue_id' => $this->syncQueueId,
            ]);
            return;
        }

        // Mark as syncing and increment attempts
        $syncQueue->incrementAttempt();

        try {
            // Get the FAQ (with trashed for delete operations)
            $faq = Faq::withTrashed()->find($this->faqId);
            
            if (!$faq && $this->syncType !== 'delete') {
                throw new \Exception('FAQ not found');
            }

            Log::info('Starting FAQ sync', [
                'faq_id' => $this->faqId,
                'sync_type' => $this->syncType,
                'attempt' => $syncQueue->attempts,
            ]);

            // Perform the sync
            $syncService->syncFaq($faq, $this->syncType);

            // Mark as synced
            $syncQueue->markAsSynced();

            // Update FAQ sync metadata
            if ($faq && $this->syncType !== 'delete') {
                $faq->updateSyncHash();
            }

            Log::info('FAQ sync completed successfully', [
                'faq_id' => $this->faqId,
                'sync_type' => $this->syncType,
            ]);

        } catch (\Exception $e) {
            Log::error('FAQ sync failed', [
                'faq_id' => $this->faqId,
                'sync_type' => $this->syncType,
                'attempt' => $syncQueue->attempts,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update sync queue with error
            $syncQueue->update([
                'sync_status' => $syncQueue->attempts >= 3 ? 'failed' : 'pending',
                'last_error' => $e->getMessage(),
            ]);

            // Re-throw to trigger Laravel's retry mechanism if not max attempts
            if ($syncQueue->attempts < 3) {
                throw $e;
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('FAQ sync job failed permanently', [
            'faq_id' => $this->faqId,
            'sync_type' => $this->syncType,
            'sync_queue_id' => $this->syncQueueId,
            'error' => $exception->getMessage(),
        ]);

        // Mark as failed in database
        $syncQueue = FaqSyncQueue::find($this->syncQueueId);
        if ($syncQueue) {
            $syncQueue->markAsFailed($exception->getMessage());
        }
    }
}