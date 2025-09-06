<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRoutingHistory extends Model
{
    protected $fillable = [
        'ticket_id',
        'staff_id',
        'status',
        'routed_at',
        'notes',
    ];

    protected $casts = [
        'routed_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}