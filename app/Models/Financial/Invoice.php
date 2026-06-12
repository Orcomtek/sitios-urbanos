<?php

namespace App\Models\Financial;

use App\Models\Core\Community;
use App\Models\Core\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasUuids, SoftDeletes;

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

    /**
     * Scope a query to only include invoices of a given community.
     */
    public function scopeForCommunity(Builder $query, int $communityId): Builder
    {
        return $query->where('community_id', $communityId);
    }
}
