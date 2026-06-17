<?php

namespace App\Models\Financial;

use App\Models\BillingConcept;
use App\Models\Community;
use App\Models\Traits\TenantScoped;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialAdjustment extends Model
{
    use HasFactory, HasUuids, SoftDeletes, TenantScoped;

    protected $guarded = ['id'];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function billingConcept(): BelongsTo
    {
        return $this->belongsTo(BillingConcept::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
