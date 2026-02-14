<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'description'];
    
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
}