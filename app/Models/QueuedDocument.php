<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueuedDocument extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
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

    /**
     * Get the file content from database or filesystem
     */
    public function getFileContent(): string
    {
        // If file_path exists, read from filesystem
        if ($this->file_path) {
            $filePath = storage_path('app/' . $this->file_path);
            if (file_exists($filePath)) {
                return file_get_contents($filePath);
            }
        }
        
        // Fall back to database content
        return $this->file_content ?? '';
    }

    /**
     * Set the file content by saving to filesystem
     */
    public function setFileContent(string $content): void
    {
        // Ensure the queued_documents directory exists
        $directory = storage_path('app/queued_documents');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Generate unique filename - ensure file_type is valid extension
        $fileExtension = $this->file_type;
        if (empty($fileExtension) || $fileExtension === 'text/plain') {
            $fileExtension = 'txt';
        }
        
        $filename = $this->file_name . '_' . uniqid() . '.' . $fileExtension;
        $filePath = 'queued_documents/' . $filename;
        
        // Save to filesystem
        $storagePath = storage_path('app/' . $filePath);
        file_put_contents($storagePath, $content);
        
        // Update the file_path
        $this->file_path = $filePath;
    }
}
