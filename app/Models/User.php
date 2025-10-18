<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        'role_id',
        'category',
        'profile_photo',
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
        ];
    }

    /**
     * Eloquent relationship to Role model.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
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
        // If legacy 'role' attribute is present in attributes (pre-migration), return it.
        if (array_key_exists('role', $this->attributes) && !is_null($this->attributes['role'])) {
            return $this->attributes['role'];
        }

        // If relation is loaded, use it.
        if ($this->relationLoaded('role')) {
            $r = $this->getRelation('role');
            return $r ? $r->name : null;
        }

        // Fallback: try to resolve via role_id
        if (!empty($this->attributes['role_id'])) {
            $role = Role::find($this->attributes['role_id']);
            return $role ? $role->name : null;
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
        if ($this->relationLoaded('role')) {
            return $this->getRelation('role');
        }
 
        if (!empty($this->attributes['role_id'])) {
            return Role::find($this->attributes['role_id']);
        }
 
        return null;
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
     * Helper to check if the user is the Primary Administrator.
     * Keeps previous behavior checks like ($user->role === 'Primary Administrator') functional.
     *
     * @return bool
     */
    public function isPrimaryAdministrator(): bool
    {
        return strtolower((string) ($this->role ?? '')) === 'primary administrator';
    }
}
