<?php

namespace App\Actions\Financial;

use App\Models\Financial\FinancialAdjustment;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class IssueAccountingNoteAction
{
    /**
     * Issue a credit or debit note for a unit's ledger.
     */
    public function execute(Unit $unit, array $data, int $processorId): FinancialAdjustment
    {
        if (! isset($data['amount']) || $data['amount'] <= 0) {
            throw new InvalidArgumentException('Adjustment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($unit, $data, $processorId) {
            return $unit->financialAdjustments()->create([
                'community_id' => $unit->community_id,
                'type' => $data['type'], // 'credit' or 'debit'
                'amount' => $data['amount'],
                'billing_concept_id' => $data['billing_concept_id'],
                'invoice_id' => $data['invoice_id'] ?? null,
                'description' => $data['description'],
                'processed_by' => $processorId,
            ]);
        });
    }
}
