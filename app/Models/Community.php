<?php

namespace App\Models;

use Database\Factories\CommunityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    /** @use HasFactory<CommunityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'status',
        'saas_settings',
    ];

    /**
     * The users that belong to the community.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'unit_id', 'invited_by_user_id', 'resident_role'])
            ->withTimestamps();
    }

    /**
     * The units that belong to the community.
     *
     * @return HasMany<Unit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * The residents that belong to the community.
     *
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'saas_settings' => 'array',
        ];
    }

    /**
     * Check if the community has a specific SaaS feature enabled.
     */
    public function hasFeature(string $feature): bool
    {
        $settings = $this->saas_settings ?? [];

        return $settings[$feature] ?? false;
    }
}
