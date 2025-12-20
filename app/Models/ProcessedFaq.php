<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessedFaq extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'staff_id',
        'question',
        'response',
        'processed_at',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}