<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsTicketSnapshot extends Model
{
    protected $table = 'analytics_ticket_snapshot';

    protected $fillable = [
        'ticket_id',
        'status',
        'assigned_agent_id',
        'category',
        'snapshot_date',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }
}
