<?php

namespace App\Listeners;

use App\Events\FaqCreated;
use App\Events\FaqUpdated;
use App\Events\FaqDeleted;
use App\Events\FaqEnabled;
use App\Events\FaqDisabled;
use App\Models\FaqSyncQueue;
use App\Jobs\SyncFaqToRasa;
use Illuminate\Support\Facades\Log;

class QueueFaqSync
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        // Determine sync type based on event class
        $syncType = match(get_class($event)) {
            FaqCreated::class => 'create',
            FaqUpdated::class => 'update',
            FaqDeleted::class => 'delete',
            FaqEnabled::class => 'enable',
            FaqDisabled::class => 'disable',
            default => 'update',
        };

        try {
            // Create sync queue entry
            $syncQueue = FaqSyncQueue::create([
                'faq_id' => $event->faq->id,
                'sync_type' => $syncType,
                'sync_status' => 'pending',
                'attempts' => 0,
            ]);

            Log::info('FAQ sync queued', [
                'faq_id' => $event->faq->id,
                'sync_type' => $syncType,
                'sync_queue_id' => $syncQueue->id,
            ]);

            // Dispatch immediate sync job
            SyncFaqToRasa::dispatch($event->faq->id, $syncType, $syncQueue->id);

        } catch (\Exception $e) {
            Log::error('Failed to queue FAQ sync', [
                'faq_id' => $event->faq->id,
                'sync_type' => $syncType,
                'error' => $e->getMessage(),
            ]);
        }
    }
}