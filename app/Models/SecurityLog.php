<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SecurityLog extends Model
{
    use TenantScoped;

    public const UPDATED_AT = null; // Append-only

    protected $fillable = [
        'community_id',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function ($model) {
            return false; // Prevent updates
        });

        static::deleting(function ($model) {
            return false; // Prevent deletes
        });

        static::created(function ($model) {
            \App\Events\TenantActivityUpdated::dispatch((string) $model->community_id);
        });
    }

    /**
     * @return BelongsTo<Community, $this>
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
