<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'starts_at',
        'expires_at',
        'role_id',
        'created_by',
        'pinned'
    ];

    protected $casts = [
        'pinned' => 'boolean',
        'starts_at' => 'datetime', // 👈 Cast to Carbon
        'expires_at' => 'datetime'  // 👈 Cast to Carbon
    ];

    protected $dates = ['deleted_at'];

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

    /**
     * Scope a query to only include announcements that are currently active (not expired).
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('expires_at') // If no expiration, it lives forever
                ->orWhere('expires_at', '>', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('starts_at')
                ->orWhere('starts_at', '<=', $now);
        });
    }
}
