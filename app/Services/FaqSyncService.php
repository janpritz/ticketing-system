<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaqSyncService
{
    /**
     * Trigger FAQ cache refresh in Rasa server.
     */
    public function syncFaq(?Faq $faq, string $syncType): void
    {
        $url = config('services.faq_refetch.url');
        $secret = config('services.faq_refetch.secret');

        if (!$url) {
            throw new \Exception('FAQ_REFETCH_URL not configured');
        }

        Log::info('Triggering FAQ cache refetch in Rasa', [
            'sync_type' => $syncType,
            'faq_id' => $faq?->id,
            'url' => $url,
        ]);

        // Make HTTP request to Rasa refetch endpoint
        $response = Http::timeout(30)
            ->withHeaders([
                'X-FAQ-REFETCH-TOKEN' => $secret,
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'triggered_by' => $syncType,
                'faq_id' => $faq?->id,
            ]);

        if (!$response->successful()) {
            $error = $response->json('error') ?? $response->body();
            throw new \Exception("Rasa refetch failed: {$error}");
        }

        $result = $response->json();

        if (!($result['ok'] ?? false)) {
            $error = $result['error'] ?? 'Unknown error';
            throw new \Exception("Rasa refetch returned error: {$error}");
        }

        Log::info('FAQ cache refetch triggered successfully', [
            'sync_type' => $syncType,
            'faq_id' => $faq?->id,
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

    /**
     * Send all FAQs to Rasa via batch endpoint (alternative to DB access).
     */
    public function sendBatchToRasa(): array
    {
        // Get all enabled FAQs
        $faqs = Faq::where('response_disabled', false)
            ->select('id', 'intent', 'description', 'response')
            ->get();

        if ($faqs->isEmpty()) {
            return ['ok' => true, 'message' => 'No FAQs to sync', 'count' => 0];
        }

        $url = config('services.faq_sync.url');
        $secret = config('services.faq_sync.secret');

        if (!$url) {
            throw new \Exception('FAQ_UPDATER_URL not configured');
        }

        Log::info('Sending batch FAQ data to Rasa', [
            'faq_count' => $faqs->count(),
            'url' => $url,
        ]);

        // Transform FAQs for Rasa
        $faqsForRasa = $faqs->map(function ($faq, $index) {
            return [
                'id' => $faq->id,
                'intent' => $faq->intent,
                'description' => $faq->description ?? '',
                'sync_type' => 'update'
            ];
        });

        // Make HTTP request to Rasa batch endpoint
        $response = Http::timeout(60)
            ->withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'faqs' => $faqsForRasa->toArray()
            ]);

        if (!$response->successful()) {
            $error = $response->json('error') ?? $response->body();
            throw new \Exception("Rasa batch update failed: {$error}");
        }

        $result = $response->json();

        if (!($result['ok'] ?? false)) {
            $error = $result['error'] ?? 'Unknown error';
            throw new \Exception("Rasa batch update returned error: {$error}");
        }

        Log::info('Batch FAQ data sent to Rasa successfully', [
            'faq_count' => $faqs->count(),
            'result' => $result,
        ]);

        return $result;
    }
}