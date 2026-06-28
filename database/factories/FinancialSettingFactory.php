<?php

namespace Database\Factories;

use App\Enums\CommissionType;
use App\Models\Community;
use App\Models\FinancialSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialSetting>
 */
class FinancialSettingFactory extends Factory
{
    protected $model = FinancialSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'base_budget' => fake()->randomFloat(2, 100000, 5000000),
            'late_fee_interest_rate' => fake()->randomFloat(2, 0, 10),
            'billing_day' => 1,
            'due_day' => 10,
            'bank_account_details' => null,
            'epayco_allied_account_id' => null,
            'commission_type' => CommissionType::Fixed,
            'commission_value' => 1500,
            'dunning_policies' => null,
        ];
    }

    /**
     * State: dunning enabled with default restrictions.
     */
    public function withDunningEnabled(int $graceDays = 0): static
    {
        return $this->state(fn (array $attrs) => [
            'dunning_policies' => [
                'enabled' => true,
                'grace_period_days' => $graceDays,
                'restrictions' => [
                    'restrict_ecosystem' => true,
                    'restrict_pqrs' => true,
                    'restrict_moving_permits' => true,
                    'restrict_amenities' => false,
                ],
            ],
        ]);
    }
}
