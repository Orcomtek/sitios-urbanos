<?php

namespace App\Actions\Financial;

use App\Enums\PaymentStatus;
use App\Models\Unit;

class CalculateUnitBalanceAction
{
    /**
     * Calculate the net balance of a unit.
     * Positive balance implies debt owed by the unit.
     * Negative balance implies a credit in favor of the unit.
     */
    public function execute(Unit $unit): float
    {
        $invoicesTotal = $unit->invoices()->sum('total');
        $debitNotesTotal = $unit->financialAdjustments()->where('type', 'debit')->sum('amount');

        $paymentsTotal = $unit->payments()->where('status', PaymentStatus::CONFIRMED)->sum('amount');
        $creditNotesTotal = $unit->financialAdjustments()->where('type', 'credit')->sum('amount');

        return (float) ($invoicesTotal + $debitNotesTotal - $paymentsTotal - $creditNotesTotal);
    }
}
