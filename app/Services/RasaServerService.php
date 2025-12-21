<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RasaServerService
{
    public static function isServerOnline(): bool
    {
        try {
            $rasaUrl = config('services.faq_sync.url');
            if (!$rasaUrl) {
                return false;
            }

            // Remove /sync-faqs from the end to get base URL
            $baseUrl = rtrim($rasaUrl, '/sync-faqs');

            // Try to ping the server by making a simple request to /health or similar
            $response = Http::timeout(5)->get($baseUrl . '/health');

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Rasa server status check failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function uploadDocument(string $fileName, string $fileContent, string $fileType = 'txt'): array
    {
        $rasaUrl = config('services.faq_sync.url');
        $baseUrl = rtrim($rasaUrl, '/sync-faqs');
        $uploadUrl = $baseUrl . '/upload-file';
        $secret = config('services.faq_sync.secret');

        Log::info('Rasa upload attempt', [
            'upload_url' => $uploadUrl,
            'secret_length' => strlen($secret),
            'file_name' => $fileName,
            'file_type' => $fileType,
            'content_length' => strlen($fileContent)
        ]);

        $response = Http::withHeaders([
            'X-FAQ-UPDATER-TOKEN' => $secret,
            'X-Requested-With' => 'XMLHttpRequest',
            'Content-Type' => 'application/json'
        ])->post($uploadUrl, [
            'file_name' => $fileName,
            'file_content' => $fileContent,
            'file_type' => $fileType
        ]);

        Log::info('Rasa upload response', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if (!$response->successful()) {
            throw new \Exception('Upload failed with status: ' . $response->status());
        }

        return $response->json();
    }
}