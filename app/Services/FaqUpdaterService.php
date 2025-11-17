<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaqUpdaterService
{
    protected string $updaterUrl;
    protected ?string $secret;

    public function __construct()
    {
        $url = config('services.faqs_updater.url');
        if (empty($url)) {
            throw new \Exception('FAQ updater URL not configured');
        }
        $this->updaterUrl = $url;
        $this->secret = config('services.faqs_updater.secret');
    }

    /**
     * Update FAQs in the external updater service.
     *
     * @param string $intent
     * @param string $description
     * @param bool $restartActions
     * @return array
     * @throws \Exception
     */
    public function updateFaq(string $intent, string $description, bool $restartActions = true): array
    {

        $payload = [
            'intent' => $intent,
            'description' => $description,
            'restart_actions' => $restartActions,
        ];

        $headers = [];
        if (!empty($this->secret)) {
            $headers['X-FAQ-UPDATER-TOKEN'] = $this->secret;
        }

        try {
            $response = Http::withHeaders($headers)->timeout(8)->post($this->updaterUrl, $payload);
        } catch (\Throwable $e) {
            Log::error("FAQ updater request failed for intent={$intent}: " . $e->getMessage());
            throw new \Exception('Failed to contact updater service');
        }

        if (!$response->ok()) {
            $body = (string) $response->body();
            Log::error("FAQ updater returned non-200 for intent={$intent}: HTTP {$response->status()} body={$body}");
            throw new \Exception("Updater service error: HTTP {$response->status()}");
        }

        try {
            $json = $response->json();
        } catch (\Throwable $e) {
            Log::error("FAQ updater returned invalid JSON for intent={$intent}: " . $e->getMessage());
            throw new \Exception('Updater returned invalid JSON');
        }

        if (empty($json['ok'])) {
            Log::error("FAQ updater reported failure for intent={$intent}: " . json_encode($json));
            throw new \Exception('Updater reported failure');
        }

        return $json;
    }
}