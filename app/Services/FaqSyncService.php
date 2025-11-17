<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaqSyncService
{
    /**
     * Sync a single FAQ to Rasa server.
     */
    public function syncFaq(?Faq $faq, string $syncType): void
    {
        $url = config('services.faq_updater.url');
        $secret = config('services.faq_updater.secret');

        if (!$url) {
            throw new \Exception('FAQ_UPDATER_URL not configured');
        }

        // For delete operations, we might not have the FAQ object
        if ($syncType === 'delete') {
            $this->deleteFaqFromRasa($faq);
            return;
        }

        if (!$faq) {
            throw new \Exception('FAQ object required for non-delete operations');
        }

        // Prepare payload
        $payload = [
            'intent' => $faq->intent,
            'description' => $faq->description ?? '',
            'restart_actions' => false, // Don't restart on individual syncs
        ];

        Log::info('Syncing FAQ to Rasa', [
            'faq_id' => $faq->id,
            'intent' => $faq->intent,
            'sync_type' => $syncType,
            'url' => $url,
        ]);

        // Make HTTP request to Rasa updater
        $response = Http::timeout(30)
            ->withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'Content-Type' => 'application/json',
            ])
            ->post($url, $payload);

        if (!$response->successful()) {
            $error = $response->json('error') ?? $response->body();
            throw new \Exception("Rasa sync failed: {$error}");
        }

        $result = $response->json();
        
        if (!($result['ok'] ?? false)) {
            $error = $result['error'] ?? 'Unknown error';
            throw new \Exception("Rasa sync returned error: {$error}");
        }

        Log::info('FAQ synced to Rasa successfully', [
            'faq_id' => $faq->id,
            'result' => $result,
        ]);
    }

    /**
     * Delete FAQ from Rasa server.
     */
    protected function deleteFaqFromRasa(?Faq $faq): void
    {
        $url = config('services.faq_deleter.url');
        $secret = config('services.faq_deleter.secret');

        if (!$url) {
            throw new \Exception('FAQ_DELETER_URL not configured');
        }

        if (!$faq) {
            throw new \Exception('FAQ object required for delete operation');
        }

        $payload = [
            'intent' => $faq->intent,
            'restart_actions' => false,
        ];

        Log::info('Deleting FAQ from Rasa', [
            'faq_id' => $faq->id,
            'intent' => $faq->intent,
            'url' => $url,
        ]);

        $response = Http::timeout(30)
            ->withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'Content-Type' => 'application/json',
            ])
            ->post($url, $payload);

        if (!$response->successful()) {
            $error = $response->json('error') ?? $response->body();
            throw new \Exception("Rasa delete failed: {$error}");
        }

        $result = $response->json();
        
        if (!($result['ok'] ?? false)) {
            $error = $result['error'] ?? 'Unknown error';
            throw new \Exception("Rasa delete returned error: {$error}");
        }

        Log::info('FAQ deleted from Rasa successfully', [
            'faq_id' => $faq->id,
            'result' => $result,
        ]);
    }

    /**
     * Sync multiple FAQs in batch.
     */
    public function syncBatch(array $faqs): array
    {
        $results = [];
        
        foreach ($faqs as $faq) {
            try {
                $this->syncFaq($faq['faq'], $faq['sync_type']);
                $results[] = [
                    'faq_id' => $faq['faq']->id,
                    'success' => true,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'faq_id' => $faq['faq']->id,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}