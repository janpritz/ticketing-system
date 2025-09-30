<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaqRevision extends Model
{
    protected $table = 'faq_revisions';

    protected $fillable = [
        'faq_id',
        'intent',
        'response',
        'user_id',
        'action',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function faq(): BelongsTo
    {
        // Ensure we can access the related FAQ even if it was soft-deleted.
        return $this->belongsTo(Faq::class, 'faq_id')->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}