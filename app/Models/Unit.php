<?php

namespace App\Models;

use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $fillable = [
        'identifier',
        'property_type',
        'status',
        'has_parking',
        'parking_count',
        'parking_identifiers',
        'has_storage',
        'storage_count',
        'storage_identifiers',
    ];

    protected function casts(): array
    {
        return [
            'has_parking' => 'boolean',
            'has_storage' => 'boolean',
            'parking_identifiers' => 'array',
            'storage_identifiers' => 'array',
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
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }
}
