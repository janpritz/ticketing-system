<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentimentAnalysis extends Model
{
    protected $table = 'sentiment_analysis';

    protected $fillable = [
        'ticket_id',
        'sentiment_score',
        'general_topic',
        'analysis_json',
        'processed_at',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
