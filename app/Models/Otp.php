<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function isValid()
    {
        return !$this->verified_at && now()->lessThanOrEqualTo($this->expires_at);
    }

    public function verify($code)
    {
        return $this->otp_code === $code && $this->isValid();
    }
}
