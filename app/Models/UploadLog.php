<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadLog extends Model
{
    protected $table = 'upload_logs';

    protected $fillable = [
        'staff_id',
        'file_name',
        'file_size',
        'upload_date',
        'server_recieved_date'
    ];

    protected $casts = [
        'upload_date' => 'datetime',
        'server_recieved_date' => 'datetime',
        'file_size' => 'integer',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function scopeForUser($query, $user)
    {
        // Use the ID check for 'Primary Administrator'
        if ((int) ($user->role_id ?? 0) !== 1) {
            return $query->where('staff_id', $user->id);
        }
        return $query;
    }
}
