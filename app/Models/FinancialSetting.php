<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialSetting extends Model
{
    protected $fillable = [
        'community_id',
        'base_budget',
        'late_fee_interest_rate',
        'billing_day',
        'due_day',
        'bank_account_details',
    ];

    protected $casts = [
        'base_budget' => 'decimal:2',
        'late_fee_interest_rate' => 'decimal:2',
        'billing_day' => 'integer',
        'due_day' => 'integer',
        'bank_account_details' => 'array',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
