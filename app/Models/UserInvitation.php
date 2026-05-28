<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a secure random token.
     */
    public static function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
