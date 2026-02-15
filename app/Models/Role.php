<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'description',
    ];

    /**
     * Department that this role belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Users that belong to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Tickets that belong to this role.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}