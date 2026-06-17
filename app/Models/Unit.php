<?php

namespace App\Models;

use App\Models\Financial\FinancialAdjustment;
use App\Models\Financial\Invoice;
use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    // EL ARRAY QUE ANTIGRAVITY BORRÓ POR ACCIDENTE
    protected $fillable = [
        'community_id',
        'identifier',
        'property_type',
        'status',
        'amenities',
        'coefficient',
    ];

    // EL CASTEO DE LA NUEVA COLUMNA JSONB
    protected function casts(): array
    {
        return [
            'amenities' => 'array',
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

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<FinancialAdjustment, $this>
     */
    public function financialAdjustments(): HasMany
    {
        return $this->hasMany(FinancialAdjustment::class);
    }
}
