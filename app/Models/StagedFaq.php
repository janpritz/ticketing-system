<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StagedFaq extends Model
{
    protected $fillable = [
        'ticket_id',
        'general_topic',
        'semantic_key',
        'suggested_q',
        'suggested_a',
        'status',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
