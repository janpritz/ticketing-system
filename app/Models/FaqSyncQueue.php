<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaqSyncQueue extends Model
{
    protected $table = 'faq_sync_queue';

    protected $fillable = [
        'faq_id',
        'sync_status',
        'sync_type',
        'attempts',
        'last_attempt_at',
        'last_error',
        'synced_at',
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'synced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Get the FAQ that owns this sync queue entry.
     */
    public function faq(): BelongsTo
    {
        return $this->belongsTo(Faq::class);
    }

    /**
     * Scope to get pending syncs.
     */
    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    /**
     * Scope to get failed syncs.
     */
    public function scopeFailed($query)
    {
        return $query->where('sync_status', 'failed');
    }

    /**
     * Scope to get syncs that can be retried.
     */
    public function scopeRetryable($query)
    {
        return $query->where('sync_status', 'pending')
            ->where('attempts', '<', 3);
    }

    /**
     * Check if this sync can be retried.
     */
    public function canRetry(): bool
    {
        return $this->sync_status === 'pending' && $this->attempts < 3;
    }

    /**
     * Mark this sync as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'sync_status' => 'failed',
            'last_error' => $error,
        ]);
    }

    /**
     * Mark this sync as synced.
     */
    public function markAsSynced(): void
    {
        $this->update([
            'sync_status' => 'synced',
            'synced_at' => now(),
            'last_error' => null,
        ]);
    }

    /**
     * Increment attempt counter.
     */
    public function incrementAttempt(): void
    {
        $this->increment('attempts');
        $this->update([
            'sync_status' => 'syncing',
            'last_attempt_at' => now(),
        ]);
    }
}