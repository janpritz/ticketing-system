<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'role_id',
        'created_by',
        'pinned'
    ];

    protected $casts = [
        'pinned' => 'boolean'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }
}
