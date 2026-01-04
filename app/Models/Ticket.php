<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'category_id',
        'question',
        'response',
        'recepient_id',
        'email',
        'status',
        'staff_id',
        'date_created',
        'date_closed',
        'attachments',
    ];


    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function routingHistories()
    {
        return $this->hasMany(TicketRoutingHistory::class, 'ticket_id')
            ->with('staff')
            ->orderByDesc('routed_at');
    }
}
