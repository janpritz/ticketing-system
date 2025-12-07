<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaqDeleterService
{
    protected string $deleterUrl;
    protected ?string $secret;

    public function __construct()
    {
        $url = config('services.faq_deleter.url');
        if (empty($url)) {
            throw new \Exception('FAQ deleter URL not configured');
        }
        $this->deleterUrl = $url;
        $this->secret = config('services.faq_deleter.secret');
    }

    /**
     * Delete FAQ from the external deleter service.
     *
     * @param string $intent
     * @param bool $restartActions
     * @return array
     * @throws \Exception
     */
    public function deleteFaq(string $intent, bool $restartActions = true): array
    {
        $payload = [
            'intent' => $intent,
            'restart_actions' => $restartActions,
        ];

        $headers = [];
        if (!empty($this->secret)) {
            $headers['X-FAQ-DELETER-TOKEN'] = $this->secret;
        }

        try {
            $response = Http::withHeaders($headers)->timeout(8)->post($this->deleterUrl, $payload);
        } catch (\Throwable $e) {
            Log::error("FAQ deleter request failed for intent={$intent}: " . $e->getMessage());
            throw new \Exception('Failed to contact deleter service');
        }

        if (!$response->ok()) {
            $body = (string) $response->body();
            Log::error("FAQ deleter returned non-200 for intent={$intent}: HTTP {$response->status()} body={$body}");
            throw new \Exception("Deleter service error: HTTP {$response->status()}");
        }

        try {
            $json = $response->json();
        } catch (\Throwable $e) {
            Log::error("FAQ deleter returned invalid JSON for intent={$intent}: " . $e->getMessage());
            throw new \Exception('Deleter returned invalid JSON');
        }

        if (empty($json['ok'])) {
            Log::error("FAQ deleter reported failure for intent={$intent}: " . json_encode($json));
            throw new \Exception('Deleter reported failure');
        }

        return $json;
    }
}