<?php

namespace App\Services;

use App\Models\FaqSyncQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RasaBatchSyncService
{
    /**
     * Sync a batch of FAQs to Rasa server.
     */
    public function syncBatch(Collection $syncQueueItems): array
    {
        $url = config('services.faq_updater.batch_url');
        $secret = config('services.faq_updater.secret');

        if (!$url) {
            Log::warning('FAQ_UPDATER_BATCH_URL not configured, falling back to individual syncs');
            return $this->fallbackToIndividualSync($syncQueueItems);
        }

        // Prepare batch payload
        $faqsData = $syncQueueItems->map(function ($syncItem) {
            return [
                'id' => $syncItem->faq_id,
                'intent' => $syncItem->faq->intent ?? '',
                'description' => $syncItem->faq->description ?? '',
                'sync_type' => $syncItem->sync_type,
            ];
        })->toArray();

        Log::info('Sending batch sync to Rasa', [
            'count' => count($faqsData),
            'url' => $url,
        ]);

        try {
            // Make batch HTTP request
            $response = Http::timeout(60)
                ->withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'faqs' => $faqsData,
                ]);

            if (!$response->successful()) {
                $error = $response->json('error') ?? $response->body();
                throw new \Exception("Batch sync failed: {$error}");
            }

            $result = $response->json();
            
            if (!($result['ok'] ?? false)) {
                $error = $result['error'] ?? 'Unknown error';
                throw new \Exception("Batch sync returned error: {$error}");
            }

            // Process results
            $results = $result['results'] ?? [];
            $processedResults = [];

            foreach ($results as $itemResult) {
                $faqId = $itemResult['faq_id'] ?? null;
                $success = $itemResult['success'] ?? false;
                $error = $itemResult['error'] ?? null;

                if ($faqId) {
                    $syncQueue = $syncQueueItems->firstWhere('faq_id', $faqId);
                    
                    if ($syncQueue) {
                        if ($success) {
                            $syncQueue->markAsSynced();
                            
                            // Update FAQ sync metadata
                            if ($syncQueue->faq && $syncQueue->sync_type !== 'delete') {
                                $syncQueue->faq->updateSyncHash();
                            }
                        } else {
                            $syncQueue->update([
                                'sync_status' => $syncQueue->attempts >= 3 ? 'failed' : 'pending',
                                'last_error' => $error,
                            ]);
                        }
                    }

                    $processedResults[] = [
                        'faq_id' => $faqId,
                        'success' => $success,
                        'error' => $error,
                    ];
                }
            }

            Log::info('Batch sync processed', [
                'total' => count($processedResults),
                'successful' => collect($processedResults)->where('success', true)->count(),
            ]);

            return $processedResults;

        } catch (\Exception $e) {
            Log::error('Batch sync exception', [
                'error' => $e->getMessage(),
            ]);

            // Mark all as failed with error
            foreach ($syncQueueItems as $syncItem) {
                $syncItem->update([
                    'sync_status' => $syncItem->attempts >= 3 ? 'failed' : 'pending',
                    'last_error' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Fallback to individual sync if batch endpoint not available.
     */
    protected function fallbackToIndividualSync(Collection $syncQueueItems): array
    {
        $syncService = app(FaqSyncService::class);
        $results = [];

        foreach ($syncQueueItems as $syncItem) {
            try {
                $syncService->syncFaq($syncItem->faq, $syncItem->sync_type);
                
                $syncItem->markAsSynced();
                
                if ($syncItem->faq && $syncItem->sync_type !== 'delete') {
                    $syncItem->faq->updateSyncHash();
                }

                $results[] = [
                    'faq_id' => $syncItem->faq_id,
                    'success' => true,
                ];

            } catch (\Exception $e) {
                $syncItem->update([
                    'sync_status' => $syncItem->attempts >= 3 ? 'failed' : 'pending',
                    'last_error' => $e->getMessage(),
                ]);

                $results[] = [
                    'faq_id' => $syncItem->faq_id,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}