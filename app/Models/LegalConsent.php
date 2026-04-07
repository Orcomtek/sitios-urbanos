<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalConsent extends Model
{
    public const CREATED_AT = 'agreed_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'community_id',
        'consent_type',
        'version',
        'ip_address',
        'user_agent',
        'agreed_at',
    ];

    protected function casts(): array
    {
        return [
            'agreed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Community, $this>
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
