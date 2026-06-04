<?php

namespace App\Models;

use App\Enums\ResidentType;
use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'community_id',
        'unit_id',
        'user_id',
        'full_name',
        'email',
        'phone',
        'resident_type',
        'is_active',
        'pays_administration',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'pays_administration' => 'boolean',
            'resident_type' => ResidentType::class,
        ];
    }

    /**
     * @return BelongsTo<Community, $this>
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<FamilyMember, $this>
     */
    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * @return HasMany<Vehicle, $this>
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * @return HasMany<Pet, $this>
     */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOwners($query)
    {
        return $query->where('resident_type', ResidentType::OWNER);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeTenants($query)
    {
        return $query->where('resident_type', ResidentType::TENANT);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeDependents($query)
    {
        return $query->where('resident_type', ResidentType::DEPENDENT);
    }
}
