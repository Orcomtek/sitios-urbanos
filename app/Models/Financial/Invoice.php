<?php

namespace App\Models\Financial;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Community;
use App\Models\Payment;
use App\Models\Traits\TenantScoped;
use App\Models\Unit;
use App\Models\User;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes, TenantScoped;

    protected static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }

    protected $fillable = [
        'community_id',
        'unit_id',
        'user_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'subtotal',
        'total',
        'status',
        'billing_period',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'status' => InvoiceStatus::class,
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The true outstanding balance on this invoice, accounting for partial payments.
     *
     * Subtracts all CONFIRMED payments from the invoice total so the dunning engine
     * and the resident statement always reflect the actual amount still owed — not
     * the original billed amount.
     *
     * Uses the pre-loaded `payments_sum_amount` aggregate when available (set by
     * `withSum('payments', 'amount')`) to avoid N+1 queries, falling back to
     * an in-memory sum over the loaded relationship otherwise.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        // Fast path: aggregate already loaded by the query builder.
        if (isset($this->attributes['payments_sum_amount'])) {
            $paid = (float) $this->attributes['payments_sum_amount'];
        } else {
            // Fallback: sum CONFIRMED payments from the loaded (or lazy-loaded) relation.
            $paid = $this->payments
                ->where('status', PaymentStatus::CONFIRMED)
                ->sum('amount');
        }

        return max(0.0, (float) $this->total - $paid);
    }

    /**
     * Scope a query to only include invoices of a given community.
     */
    public function scopeForCommunity(Builder $query, int $communityId): Builder
    {
        return $query->where('community_id', $communityId);
    }
}
