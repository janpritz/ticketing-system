<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use SoftDeletes;

    protected $table = 'faqs';

    protected $fillable = [
        'intent',
        'description',
        'response',
        'status',
        'response_disabled',
        'last_synced_at',
        'sync_hash',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'response_disabled' => 'boolean',
    ];

    /**
     * The event map for the model.
     */
    protected $dispatchesEvents = [
        'created' => \App\Events\FaqCreated::class,
        'updated' => \App\Events\FaqUpdated::class,
        'deleted' => \App\Events\FaqDeleted::class,
    ];

    /**
     * Revisions (audit history) for this FAQ — newest first.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(\App\Models\FaqRevision::class, 'faq_id')->orderByDesc('created_at');
    }

    /**
     * Sync queue entries for this FAQ.
     */
    public function syncQueue(): HasMany
    {
        return $this->hasMany(FaqSyncQueue::class, 'faq_id');
    }

    /**
     * Check if this FAQ needs to be synced.
     */
    public function needsSync(): bool
    {
        $currentHash = $this->calculateSyncHash();
        return $this->sync_hash !== $currentHash;
    }

    /**
     * Calculate a hash of the FAQ's syncable attributes.
     */
    public function calculateSyncHash(): string
    {
        return hash('sha256', json_encode([
            'intent' => $this->intent,
            'description' => $this->description,
            'response' => $this->response,
            'status' => $this->status,
            'response_disabled' => $this->response_disabled,
        ]));
    }

    /**
     * Update the sync hash to current state.
     */
    public function updateSyncHash(): void
    {
        $this->update([
            'sync_hash' => $this->calculateSyncHash(),
            'last_synced_at' => now(),
        ]);
    }

    /**
     * Get pending sync queue entries.
     */
    public function pendingSyncs()
    {
        return $this->syncQueue()->where('sync_status', 'pending');
    }

    /**
     * Check if FAQ has any pending syncs.
     */
    public function hasPendingSyncs(): bool
    {
        return $this->pendingSyncs()->exists();
    }
}