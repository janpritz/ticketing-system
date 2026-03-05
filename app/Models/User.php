<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Department;
use App\Models\UserRole;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * Note: we now prefer 'role_id' (foreign key) instead of 'role' string.
     * Keep 'role' out of fillable to avoid accidentally writing the old string field.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo',
        'department_id',
        'verification_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Department that this user belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Roles that this user has.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * UserRole entries for this user.
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Backwards-compatible singular role() method.
     * Returns the first role from the user's roles collection.
     */
    public function role()
    {
        return $this->roles()->first();
    }

    /**
     * Backwards-compatible accessor so existing code that uses $user->role
     * (expecting a string) continues to work.
     *
     * If a legacy 'role' attribute exists (pre-migration), return it.
     * Otherwise, return the linked role's name.
     *
     * @param  mixed $value
     * @return string|null
     */
    public function getRoleAttribute($value)
    {
        // Try to resolve via first role (from user_roles table)
        $firstRole = $this->role();
        if ($firstRole) {
            return $firstRole->name;
        }

        return null;
    }

    /**
     * A convenience accessor to get the Role model instance (if needed explicitly).
     * Use $user->roleModel to avoid confusion with $user->role (string).
     *
     * @return Role|null
     */
    public function getRoleModelAttribute()
    {
        return $this->role();
    }

    /**
     * Tickets assigned to this user (as staff).
     *
     * This relation is used for calculating current open-ticket load.
     *
     * @return HasMany
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(\App\Models\Ticket::class, 'staff_id');
    }

    /**
     * Documents uploaded by this user (staff-owned documents).
     *
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(\App\Models\Document::class, 'staff_id');
    }

    /**
     * Check if the user has the Primary Administrator role.
     */
    public function isPrimaryAdmin(): bool
    {
        // This checks the pivot table directly without loading all role data
        return $this->roles()->where('roles.id', 1)->exists();
    }
}
