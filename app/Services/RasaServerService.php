<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RasaServerService
{
    public static function isServerOnline(): bool
    {
        try {
            $rasaUrl = config('services.faq_list_docs.url');
            if (!$rasaUrl) {
                return false;
            }

            // Use /list-docs endpoint to check server status (it exists and returns 200 when server is online)
            $response = Http::timeout(10)->withHeaders([
                'X-FAQ-UPDATER-TOKEN' => config('services.faq_list_docs.secret'),
                'X-Requested-With' => 'XMLHttpRequest'
            ])->get($rasaUrl);

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
            'content_length' => strlen($fileContent),
            'request_data' => [
                'file_name' => $fileName,
                'file_content' => substr($fileContent, 0, 100) . (strlen($fileContent) > 100 ? '...' : ''),
                'file_type' => $fileType
            ]
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
            'body' => $response->body(),
            'headers' => $response->headers(),
            'successful' => $response->successful()
        ]);

        if (!$response->successful()) {
            Log::error('Upload failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Upload failed with status: ' . $response->status() . ', body: ' . $response->body());
        }

        return $response->json();
    }

    public static function deleteFile(string $fileName): array
    {
        $rasaUrl = config('services.faq_sync.url');
        $baseUrl = rtrim($rasaUrl, '/sync-faqs');
        $deleteUrl = $baseUrl . '/delete-file';
        $secret = config('services.faq_sync.secret');

        Log::info('Rasa delete-file attempt', [
            'delete_url' => $deleteUrl,
            'file_name' => $fileName,
        ]);

        $response = Http::withHeaders([
            'X-FAQ-UPDATER-TOKEN' => $secret,
            'X-Requested-With' => 'XMLHttpRequest',
            'Content-Type' => 'application/json'
        ])->post($deleteUrl, [
            'file_name' => $fileName,
        ]);

        Log::info('Rasa delete-file response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'successful' => $response->successful(),
        ]);

        if (!$response->successful()) {
            throw new \Exception('Delete failed with status: ' . $response->status() . ', body: ' . $response->body());
        }

        return $response->json();
    }
}
