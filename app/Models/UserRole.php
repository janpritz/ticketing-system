<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
        'department_id',
        'is_primary_role',
    ];

    protected function casts(): array
    {
        return [
            'is_primary_role' => 'boolean',
        ];
    }

    /**
     * User that this user role belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Role that this user role belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if this user role is Primary Administrator (role_id = 1).
     */
    public function isPrimaryAdministrator(): bool
    {
        return $this->role_id === 1;
    }
}
