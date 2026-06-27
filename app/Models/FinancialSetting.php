<?php

namespace App\Models;

use App\Enums\CommissionType;
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
        'epayco_allied_account_id',
        'commission_type',
        'commission_value',
    ];

    protected function casts(): array
    {
        return [
            'base_budget' => 'decimal:2',
            'late_fee_interest_rate' => 'decimal:2',
            'billing_day' => 'integer',
            'due_day' => 'integer',
            'bank_account_details' => 'array',
            'commission_type' => CommissionType::class,
            'commission_value' => 'integer',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
