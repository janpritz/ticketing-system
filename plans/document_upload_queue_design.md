# Document Upload Queue Design for Rasa Server

## Overview

This design implements a local storage solution for queuing document uploads to the Rasa server when it is offline. The current implementation fails synchronously when the server is unavailable. The new system will queue uploads locally and retry them when the server becomes available.

**Role-Based Access**: Documents are automatically assigned to the role of the user who created them. Users with the same role can view, edit, and manage these documents.

## Current Implementation Analysis

- **Controllers**: `StaffKnowledgebaseController` and `AdminController` handle announcement uploads
- **Upload Method**: Direct HTTP POST to Rasa server at port 5001 using `/update-document` endpoint
- **Failure Mode**: Throws exceptions and returns 500 errors when server is offline
- **Documents**: Primarily announcements stored in `Announcements.txt`, but design supports general documents

## Proposed Solution Architecture

### 1. Database Schema

Create a new table `queued_documents` to store pending uploads:

```sql
CREATE TABLE queued_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_content LONGTEXT NOT NULL,
    file_type VARCHAR(10) NOT NULL DEFAULT 'txt',
    status ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    uploaded_by BIGINT UNSIGNED NOT NULL,
    assigned_roles JSON NULL COMMENT 'Array of role IDs that can access this document',
    operation_type ENUM('create', 'update', 'delete') NOT NULL DEFAULT 'create',
    document_id INT NULL COMMENT 'For updates/deletes, the ID of the document being modified',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    uploaded_at TIMESTAMP NULL,
    error_message TEXT NULL,
    retry_count INT DEFAULT 0,
    next_retry_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_next_retry (next_retry_at),
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);
```

### 2. Eloquent Model

**File**: `app/Models/QueuedDocument.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueuedDocument extends Model
{
    protected $fillable = [
        'file_name',
        'file_content',
        'file_type',
        'status',
        'uploaded_by',
        'assigned_roles',
        'operation_type',
        'document_id',
        'uploaded_at',
        'error_message',
        'retry_count',
        'next_retry_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'retry_count' => 'integer',
        'assigned_roles' => 'array',
        'document_id' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeReadyForRetry($query)
    {
        return $query->where('status', 'failed')
                    ->where('next_retry_at', '<=', now());
    }
}
```

### 3. Server Status Check Service

**File**: `app/Services/RasaServerService.php`

```php
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

            // Try to ping the server by making a simple request
            $response = Http::timeout(5)->get($rasaUrl);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Rasa server status check failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function uploadDocument(string $fileName, string $fileContent, string $fileType = 'txt'): array
    {
        $rasaUrl = config('services.faq_list_docs.url');
        $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);
        $secret = config('services.faq_list_docs.secret');

        $response = Http::withHeaders([
            'X-FAQ-UPDATER-TOKEN' => $secret,
            'X-Requested-With' => 'XMLHttpRequest'
        ])->post($uploadUrl, [
            'file_name' => $fileName,
            'file_content' => $fileContent,
            'file_type' => $fileType
        ]);

        if (!$response->successful()) {
            throw new \Exception('Upload failed with status: ' . $response->status());
        }

        return $response->json();
    }
}
```

### 4. Laravel Job for Processing Queue

**File**: `app/Jobs/ProcessQueuedDocument.php`

```php
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

        // For other documents, use the stored content directly
        return $this->queuedDocument->file_content;
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
                $newAnnouncement = "id: {$nextId}\ntitle: {$this->queuedDocument->file_content}\nroles: {$rolesText}\n{$this->queuedDocument->file_content}\n---------\n";

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
```

### 5. Modified Controller Logic

**Integration in Controllers** (StaffKnowledgebaseController and AdminController):

Replace direct upload logic with:

```php
// Check if server is online
if (RasaServerService::isServerOnline()) {
    // Direct upload (existing logic)
    try {
        $response = Http::withHeaders([...])->post($uploadUrl, [...]);
        // ... existing success handling
    } catch (\Exception $e) {
        // Queue for later
        \App\Models\QueuedDocument::create([
            'file_name' => 'Announcements.txt',
            'file_content' => $validated['content'], // Just the content, not the full formatted text
            'file_type' => 'txt',
            'uploaded_by' => Auth::id(),
            'assigned_roles' => [Auth::user()->role_id], // Auto-assign uploader's role
            'operation_type' => 'create', // or 'update'/'delete' based on method
            'document_id' => null, // For create, or the announcement ID for update/delete
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement queued for upload (server offline)',
            'queued' => true
        ]);
    }
} else {
    // Server offline, queue immediately
    \App\Models\QueuedDocument::create([
        'file_name' => 'Announcements.txt',
        'file_content' => $validated['content'],
        'file_type' => 'txt',
        'uploaded_by' => Auth::id(),
        'assigned_roles' => [Auth::user()->role_id], // Auto-assign uploader's role
        'operation_type' => 'create',
        'document_id' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Announcement queued for upload (server offline)',
        'queued' => true
    ]);
}
```

### 6. Periodic Retry Command

**File**: `app/Console/Commands/ProcessQueuedDocuments.php`

```php
<?php

namespace App\Console\Commands;

use App\Jobs\ProcessQueuedDocument;
use App\Models\QueuedDocument;
use Illuminate\Console\Command;

class ProcessQueuedDocuments extends Command
{
    protected $signature = 'queue:process-uploads';
    protected $description = 'Process queued document uploads to Rasa server';

    public function handle()
    {
        $pending = QueuedDocument::pending()->count();
        $failed = QueuedDocument::readyForRetry()->count();

        $this->info("Found {$pending} pending and {$failed} failed documents to process");

        // Process pending documents
        QueuedDocument::pending()->each(function ($doc) {
            ProcessQueuedDocument::dispatch($doc);
        });

        // Process failed documents ready for retry
        QueuedDocument::readyForRetry()->each(function ($doc) {
            ProcessQueuedDocument::dispatch($doc);
        });

        $this->info('Queued document processing jobs dispatched');
    }
}
```

### 7. Migration File

**File**: `database/migrations/2025_12_21_000000_create_queued_documents_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('queued_documents', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->longText('file_content');
            $table->string('file_type', 10)->default('txt');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedBigInteger('uploaded_by');
            $table->json('assigned_roles')->nullable();
            $table->enum('operation_type', ['create', 'update', 'delete'])->default('create');
            $table->integer('document_id')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('uploaded_by');
            $table->index('next_retry_at');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('queued_documents');
    }
};
```

## Implementation Steps

1. Create migration and run it
2. Create QueuedDocument model
3. Create RasaServerService
4. Create ProcessQueuedDocument job
5. Create ProcessQueuedDocuments command
6. Modify StaffKnowledgebaseController announcement methods
7. Modify AdminController announcement methods
8. Schedule the command to run periodically (e.g., every 5 minutes)
9. Test the queuing and retry logic

## Benefits

- **Reliability**: Documents are never lost due to server downtime
- **User Experience**: Users can continue working even when Rasa is offline
- **Automatic Retry**: Failed uploads are retried with exponential backoff
- **Audit Trail**: All queued operations are tracked in the database
- **Scalable**: Can handle multiple document types and operations

## Monitoring

- Add admin interface to view queued documents
- Add metrics for queue size, success/failure rates
- Log all queue operations for debugging

## Future Enhancements

- Support for different document types beyond announcements
- Batch processing for multiple documents
- Priority queuing for critical documents
- Real-time notifications when uploads complete