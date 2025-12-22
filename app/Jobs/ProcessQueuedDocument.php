<?php

namespace App\Jobs;

use App\Services\RasaServerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\DocumentChange;

class ProcessQueuedDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    protected $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function handle()
    {
        $filePath = storage_path('app/queued_documents/' . $this->fileName);

        // Check if server is online
        if (!RasaServerService::isServerOnline()) {
            $this->fail(new \Exception('Rasa server is offline'));
            return;
        }

        // Read file data from filesystem
        if (!file_exists($filePath)) {
            Log::error("Queued document file not found: {$filePath}");
            return;
        }

        $rawContent = file_get_contents($filePath);
        Log::info("Raw file content", ['content' => $rawContent]);

        $fileData = json_decode($rawContent, true);
        if (!$fileData) {
            Log::error("Invalid JSON in queued document file: {$filePath}");
            return;
        }

        Log::info("Parsed file data", [
            'file_name' => $fileData['file_name'] ?? 'missing',
            'file_content_length' => strlen($fileData['file_content'] ?? ''),
            'file_type' => $fileData['file_type'] ?? 'missing'
        ]);

        try {
            // Attempt upload
            $result = RasaServerService::uploadDocument(
                $fileData['file_name'],
                $fileData['file_content'],
                $fileData['file_type']
            );

            if ($result['ok']) {
                // Success - delete the queued file
                unlink($filePath);

                // Log document change
                DocumentChange::create([
                    'file_name' => $fileData['file_name'],
                    'action' => 'created',
                    'user_id' => $fileData['uploaded_by'],
                    'user_name' => null, // Could look up user name if needed
                    'training_required' => true,
                    'training_completed' => false,
                ]);

                Log::info("Queued document uploaded successfully: {$fileData['file_name']}");
            } else {
                throw new \Exception($result['error'] ?? 'Upload failed');
            }

        } catch (\Exception $e) {
            Log::info("Exception caught in ProcessQueuedDocument", [
                'message' => $e->getMessage(),
                'file' => $this->fileName
            ]);

            $retryCount = ($fileData['retry_count'] ?? 0) + 1;
            Log::info("Retry count: {$retryCount}");

            if ($retryCount >= 3) {
                Log::info("Marking as failed");
                // Mark as failed - could rename file or move to failed directory
                $failedPath = str_replace('queued_documents', 'failed_documents', $filePath);
                $failedDir = dirname($failedPath);
                if (!file_exists($failedDir)) {
                    mkdir($failedDir, 0755, true);
                }
                $fileData['status'] = 'failed';
                $fileData['error_message'] = $e->getMessage();
                $fileData['retry_count'] = $retryCount;
                file_put_contents($failedPath, json_encode($fileData));
                unlink($filePath);
            } else {
                Log::info("Scheduling retry");
                // Schedule next retry with exponential backoff
                $nextRetry = now()->addSeconds($this->backoff[$retryCount - 1] ?? 900);
                $fileData['retry_count'] = $retryCount;
                $fileData['error_message'] = $e->getMessage();
                $fileData['next_retry_at'] = $nextRetry->toDateTimeString();
                file_put_contents($filePath, json_encode($fileData));

                // Re-queue the job
                self::dispatch($this->fileName)->delay($nextRetry);
            }

            Log::error("Failed to upload queued document: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        $filePath = storage_path('app/queued_documents/' . $this->fileName);

        // Mark file as failed if not already handled
        if (file_exists($filePath)) {
            $fileData = json_decode(file_get_contents($filePath), true);
            if ($fileData && ($fileData['status'] ?? 'pending') !== 'completed') {
                // Move to failed directory
                $failedPath = str_replace('queued_documents', 'failed_documents', $filePath);
                $failedDir = dirname($failedPath);
                if (!file_exists($failedDir)) {
                    mkdir($failedDir, 0755, true);
                }
                $fileData['status'] = 'failed';
                $fileData['error_message'] = $exception->getMessage();
                file_put_contents($failedPath, json_encode($fileData));
                unlink($filePath);
            }
        }
    }
}
