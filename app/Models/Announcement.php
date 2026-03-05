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

    // app/Models/Announcement.php

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'announcement_roles');
    }

    /**
     * Scope a query to only include announcements visible to a specific role.
     */
    public function scopeForRole($query, $roleId)
    {
        return $query->where(function ($q) use ($roleId) {
            $q->whereHas('roles', function ($sub) use ($roleId) {
                $sub->where('roles.id', $roleId);
            })->orWhere('role_id', $roleId); // Legacy column fallback
        });
    }
}
