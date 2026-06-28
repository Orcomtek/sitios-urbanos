<?php

namespace App\Models;

use App\Enums\CommissionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialSetting extends Model
{
    use HasFactory;

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
        'dunning_policies',
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
            'dunning_policies' => 'array',
        ];
    }

    /**
     * Returns the dunning restriction flags with safe defaults.
     * Fail-open: if no policy is configured, no modules are restricted.
     *
     * @return array{enabled: bool, grace_period_days: int, restrictions: array<string, bool>}
     */
    public function getDunningPolicies(): array
    {
        $defaults = [
            'enabled' => false,
            'grace_period_days' => 0,
            'restrictions' => [
                'restrict_ecosystem' => false,
                'restrict_pqrs' => false,
                'restrict_moving_permits' => false,
                'restrict_amenities' => false,
            ],
        ];

        $stored = $this->dunning_policies ?? [];

        return array_replace_recursive($defaults, $stored);
    }

    /**
     * Whether dunning restrictions are enabled for this community.
     */
    public function hasDunningEnabled(): bool
    {
        return (bool) ($this->dunning_policies['enabled'] ?? false);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
