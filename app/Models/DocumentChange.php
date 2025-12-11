<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChange extends Model
{
    protected $fillable = [
        'file_name',
        'action',
        'user_id',
        'user_name',
        'old_content_hash',
        'new_content_hash',
        'change_timestamp',
        'training_required',
        'training_completed',
        'training_timestamp',
    ];

    protected $casts = [
        'change_timestamp' => 'datetime',
        'training_timestamp' => 'datetime',
        'training_required' => 'boolean',
        'training_completed' => 'boolean',
    ];

    /**
     * Get the user that made this change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get changes that require training.
     */
    public function scopeRequiresTraining($query)
    {
        return $query->where('training_required', true)
                    ->where('training_completed', false);
    }

    /**
     * Scope to get recent changes.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('change_timestamp', '>=', now()->subDays($days));
    }

    /**
     * Get the timestamp of the last successful training.
     */
    public static function getLastTrainingTimestamp()
    {
        return static::whereNotNull('training_timestamp')
                    ->orderBy('training_timestamp', 'desc')
                    ->value('training_timestamp');
    }

    /**
     * Check if there was a recent training within the specified minutes.
     */
    public static function hasRecentTraining($minutes = 60)
    {
        $lastTraining = static::getLastTrainingTimestamp();
        return $lastTraining && $lastTraining->diffInMinutes(now()) <= $minutes;
    }
}
