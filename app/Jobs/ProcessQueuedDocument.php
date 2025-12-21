<?php

namespace App\Jobs;

use App\Models\QueuedDocument;
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

    protected $queuedDocument;

    public function __construct(QueuedDocument $queuedDocument)
    {
        $this->queuedDocument = $queuedDocument;
    }

    public function handle()
    {
        // Check if server is online
        if (!RasaServerService::isServerOnline()) {
            $this->fail(new \Exception('Rasa server is offline'));
            return;
        }

        try {
            $content = $this->prepareContentForUpload();

            // Attempt upload
            $result = RasaServerService::uploadDocument(
                $this->queuedDocument->file_name,
                $content,
                $this->queuedDocument->file_type
            );

            if ($result['ok']) {
                // Success
                $this->queuedDocument->update([
                    'status' => 'completed',
                    'uploaded_at' => now(),
                    'error_message' => null
                ]);

                // Log document change
                DocumentChange::create([
                    'file_name' => $this->queuedDocument->file_name,
                    'action' => $this->queuedDocument->operation_type === 'delete' ? 'deleted' : 'updated',
                    'user_id' => $this->queuedDocument->uploaded_by,
                    'user_name' => $this->queuedDocument->user->name ?? null,
                    'training_required' => true,
                    'training_completed' => false,
                ]);

                Log::info("Queued document uploaded successfully: {$this->queuedDocument->file_name}");
            } else {
                throw new \Exception($result['error'] ?? 'Upload failed');
            }

        } catch (\Exception $e) {
            $retryCount = $this->queuedDocument->retry_count + 1;

            if ($retryCount >= 3) {
                $this->queuedDocument->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            } else {
                // Schedule next retry with exponential backoff
                $nextRetry = now()->addSeconds($this->backoff[$retryCount - 1] ?? 900);
                $this->queuedDocument->update([
                    'retry_count' => $retryCount,
                    'next_retry_at' => $nextRetry,
                    'error_message' => $e->getMessage()
                ]);

                // Re-queue the job
                self::dispatch($this->queuedDocument)->delay($nextRetry);
            }

            Log::error("Failed to upload queued document: {$e->getMessage()}");
            throw $e;
        }
    }

    private function prepareContentForUpload(): string
    {
        // For announcements, we need to handle different operations
        if ($this->queuedDocument->file_name === 'Announcements.txt') {
            return $this->prepareAnnouncementContent();
        }

        // For other documents, use the stored content from database or filesystem
        return $this->queuedDocument->getFileContent();
    }

    private function prepareAnnouncementContent(): string
    {
        $rasaUrl = config('services.faq_list_docs.url');
        $secret = config('services.faq_list_docs.secret');

        switch ($this->queuedDocument->operation_type) {
            case 'create':
                // For create, we need to get current announcements and append
                $listUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);
                $listResponse = Http::withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])->get($listUrl);

                $nextId = 1;
                $currentContent = '';

                if ($listResponse->successful()) {
                    $listData = $listResponse->json();
                    if ($listData['ok'] && !empty($listData['announcements'])) {
                        $maxId = max(array_column($listData['announcements'], 'id'));
                        $nextId = $maxId + 1;

                        // Get current file content
                        $downloadUrl = str_replace('/list-docs', '/download/Announcements.txt', $rasaUrl);
                        $downloadResponse = Http::withHeaders([
                            'X-FAQ-UPDATER-TOKEN' => $secret,
                            'X-Requested-With' => 'XMLHttpRequest'
                        ])->get($downloadUrl);

                        if ($downloadResponse->successful()) {
                            $currentContent = $downloadResponse->body();
                        }
                    }
                }

                // Format new announcement
                $rolesText = $this->queuedDocument->assigned_roles ? implode(',', $this->queuedDocument->assigned_roles) : 'all';
                $fileContent = $this->queuedDocument->getFileContent();
                $newAnnouncement = "id: {$nextId}\ntitle: {$fileContent}\nroles: {$rolesText}\n{$fileContent}\n---------\n";

                return $currentContent . $newAnnouncement;

            case 'update':
                // For update, fetch current, modify the specific announcement, reconstruct
                // Implementation similar to controller update logic
                // ... (detailed implementation needed)

            case 'delete':
                // For delete, fetch current, remove the specific announcement, reconstruct
                // Implementation similar to controller delete logic
                // ... (detailed implementation needed)

            default:
                return $this->queuedDocument->file_content;
        }
    }

    public function failed(\Throwable $exception)
    {
        // Mark as failed if not already handled
        if ($this->queuedDocument->status !== 'completed') {
            $this->queuedDocument->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage()
            ]);
        }
    }
}
