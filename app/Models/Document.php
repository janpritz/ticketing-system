<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'documents';

    protected $fillable = [
        'staff_id',
        'file_name',
        'rasa_doc_id',
        'file_type',
        'file_size',
        'content_hash',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
