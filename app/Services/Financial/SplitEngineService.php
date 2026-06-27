<?php

namespace App\Services\Financial;

use App\Enums\CommissionType;
use App\Exceptions\SplitConfigurationException;
use App\Models\FinancialSetting;

class SplitEngineService
{
    /**
     * Calculate the split distribution for a platform-processed payment.
     *
     * Uses integer COP math throughout to avoid floating-point rounding errors.
     * The commission is determined by the tenant's FinancialSetting; if none exists,
     * falls back to the global config('finance.commission.*') defaults.
     *
     * @param  int  $amount  Total payment amount in COP (integer).
     * @param  FinancialSetting|null  $settings  The tenant's financial configuration.
     * @param  int  $communityId  Used for exception context when allied account is missing.
     * @return array{
     *     platform_commission: int,
     *     net_amount: int,
     *     split_receiver_id: string,
     *     split_percentage: float,
     *     commission_type: string,
     *     commission_value: int,
     * }
     *
     * @throws SplitConfigurationException
     */
    public function calculate(int $amount, ?FinancialSetting $settings, int $communityId): array
    {
        $commissionType = $settings?->commission_type
            ?? CommissionType::from(config('finance.commission.type', 'fixed'));

        $commissionValue = $settings?->commission_value
            ?? (int) config('finance.commission.value', 1500);

        $alliedAccountId = $settings?->epayco_allied_account_id;

        if (! $alliedAccountId) {
            throw SplitConfigurationException::missingAlliedAccount($communityId);
        }

        $platformCommission = $this->calculateCommission($amount, $commissionType, $commissionValue);
        $netAmount = max(0, $amount - $platformCommission);

        $splitPercentage = $amount > 0
            ? round(($netAmount / $amount) * 100, 2)
            : 0.00;

        return [
            'platform_commission' => $platformCommission,
            'net_amount' => $netAmount,
            'split_receiver_id' => $alliedAccountId,
            'split_percentage' => $splitPercentage,
            'commission_type' => $commissionType->value,
            'commission_value' => $commissionValue,
        ];
    }

    /**
     * Calculate the platform commission in integer COP.
     *
     * For fixed: the commission is the configured flat amount, capped at the payment amount.
     * For percentage: commission_value is stored as hundredths (e.g., 350 = 3.50%).
     * We use floor() to avoid overcharging — the platform absorbs rounding dust.
     */
    private function calculateCommission(int $amount, CommissionType $type, int $value): int
    {
        return match ($type) {
            CommissionType::Fixed => min($value, $amount),
            CommissionType::Percentage => (int) floor($amount * ($value / 10000)),
        };
    }
}
