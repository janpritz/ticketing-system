<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'category',
        'question',
        'response',
        'recepient_id',
        'email',
        'status',
        'staff_id',
        'date_created',
        'date_closed',
    ];


    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
    public function routingHistories()
    {
        return $this->hasMany(TicketRoutingHistory::class, 'ticket_id')
            ->with('staff')
            ->orderByDesc('routed_at');
    }
}
