<?php

namespace App\Services\Financial;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\RestrictableModule;
use App\Models\Community;
use App\Models\Financial\Invoice;
use App\Models\FinancialSetting;
use App\Models\Unit;
use Illuminate\Support\Carbon;

class DunningService
{
    /**
     * Determines whether the given unit has at least one past-due invoice,
     * taking the configured grace period into account.
     *
     * A unit is considered in arrears when:
     *  - It has one or more invoices with status PENDING
     *  - The invoice due_date is strictly before (today - grace_period_days)
     *  - The community's dunning engine is enabled
     */
    public function isUnitInArrears(Unit $unit, ?FinancialSetting $setting = null): bool
    {
        $setting ??= $this->getSetting($unit->community_id);

        if (! $setting || ! $setting->hasDunningEnabled()) {
            return false;
        }

        $policies = $setting->getDunningPolicies();
        $graceDays = (int) ($policies['grace_period_days'] ?? 0);
        $cutoff = Carbon::today()->subDays($graceDays);

        return Invoice::where('unit_id', $unit->id)
            ->where('community_id', $unit->community_id)
            ->where('status', InvoiceStatus::PENDING)
            ->where('due_date', '<', $cutoff)
            ->exists();
    }

    /**
     * Returns the list of module keys (matching config/modules.php registry keys)
     * that are restricted for the given unit.
     *
     * Returns an empty array when dunning is disabled or the unit has no arrears.
     *
     * @return list<string>
     */
    public function getRestrictedModules(Unit $unit, ?FinancialSetting $setting = null): array
    {
        $setting ??= $this->getSetting($unit->community_id);

        if (! $setting || ! $this->isUnitInArrears($unit, $setting)) {
            return [];
        }

        $policies = $setting->getDunningPolicies();
        $restrictionFlags = $policies['restrictions'] ?? [];

        $restricted = [];

        foreach (RestrictableModule::cases() as $module) {
            $flag = $module->value;

            if (! empty($restrictionFlags[$flag])) {
                foreach ($module->moduleKeys() as $key) {
                    $restricted[] = $key;
                }
            }
        }

        return array_unique($restricted);
    }

    /**
     * Checks whether a specific module key is restricted for the given unit.
     */
    public function isModuleRestricted(Unit $unit, string $moduleKey, ?FinancialSetting $setting = null): bool
    {
        return in_array($moduleKey, $this->getRestrictedModules($unit, $setting), true);
    }

    /**
     * Returns full restriction context for frontend consumption.
     *
     * @return array{
     *     is_restricted: bool,
     *     restricted_modules: list<string>,
     *     oldest_due_date: string|null,
     *     total_overdue: float
     * }
     */
    public function getRestrictionContext(Unit $unit, ?FinancialSetting $setting = null): array
    {
        $setting ??= $this->getSetting($unit->community_id);

        $isRestricted = $this->isUnitInArrears($unit, $setting);
        $restrictedModules = $isRestricted ? $this->getRestrictedModules($unit, $setting) : [];

        $overdueData = ['oldest_due_date' => null, 'total_overdue' => 0.0];

        if ($isRestricted) {
            $policies = $setting?->getDunningPolicies() ?? [];
            $graceDays = (int) ($policies['grace_period_days'] ?? 0);
            $cutoff = Carbon::today()->subDays($graceDays);

            // Load overdue invoices with the sum of their CONFIRMED payments in one
            // query so the outstanding_balance accessor uses the fast path and avoids
            // N+1 queries per invoice.
            $overdueInvoices = Invoice::where('unit_id', $unit->id)
                ->where('community_id', $unit->community_id)
                ->where('status', InvoiceStatus::PENDING)
                ->where('due_date', '<', $cutoff)
                ->withSum(['payments as payments_sum_amount' => function ($q) {
                    $q->where('status', PaymentStatus::CONFIRMED);
                }], 'amount')
                ->get(['id', 'total', 'due_date']);

            // Sum the true outstanding balances, not the original billed amounts.
            $overdueData['total_overdue'] = (float) $overdueInvoices->sum(
                fn (Invoice $invoice) => $invoice->outstanding_balance
            );
            $oldest = $overdueInvoices->sortBy('due_date')->first();
            $overdueData['oldest_due_date'] = $oldest?->due_date?->toDateString();
        }

        return [
            'is_restricted' => $isRestricted,
            'restricted_modules' => $restrictedModules,
            'oldest_due_date' => $overdueData['oldest_due_date'],
            'total_overdue' => $overdueData['total_overdue'],
        ];
    }

    /**
     * Retrieves the FinancialSetting for a given community, with caching per request.
     */
    private function getSetting(int $communityId): ?FinancialSetting
    {
        return FinancialSetting::where('community_id', $communityId)->first();
    }
}
