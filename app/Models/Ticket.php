<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'role_id',
        'question',
        'response',
        'recepient_id',
        'email',
        'status',
        'staff_id',
        'date_created',
        'date_closed',
        'attachments',
        'is_processed',
        'first_viewed_at',
        'first_viewed_by',
    ];

    protected function casts(): array
    {
        return [
            'is_processed' => 'boolean',
        ];
    }


    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Role that this ticket belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Category relationship for backward compatibility.
     * @deprecated Use role() instead
     */

    public function routingHistories()
    {
        return $this->hasMany(TicketRoutingHistory::class, 'ticket_id')
            ->with('staff')
            ->orderByDesc('routed_at');
    }

    public function stagedFaqs()
    {
        return $this->hasMany(StagedFaq::class, 'ticket_id');
    }

    /**
     * Check if this ticket has been processed (analyzed with OpenAI).
     */
    public function isProcessed(): bool
    {
        return $this->is_processed === true;
    }
}
