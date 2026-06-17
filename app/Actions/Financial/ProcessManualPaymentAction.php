<?php

namespace App\Actions\Financial;

use App\Enums\PaymentStatus;
use App\Models\Financial\Invoice;
use App\Models\Payment;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessManualPaymentAction
{
    /**
     * Process a manual external payment and update the ledger.
     */
    public function execute(Unit $unit, array $data, int $processorId): Payment
    {
        if (! isset($data['amount']) || $data['amount'] <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($unit, $data, $processorId) {
            /** @var Payment $payment */
            $payment = $unit->payments()->create([
                'community_id' => $unit->community_id,
                'amount' => $data['amount'],
                'method' => $data['payment_method'],
                'external_reference' => $data['reference_number'] ?? $data['external_reference'] ?? null,
                'status' => PaymentStatus::CONFIRMED,
                'gateway_payload' => $data['gateway_payload'] ?? [],
                'processed_by' => $processorId,
                'paid_at' => now(),
                'invoice_id' => $data['invoice_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if (! empty($data['invoice_id'])) {
                $invoice = Invoice::find($data['invoice_id']);
                if ($invoice) {
                    $totalPaid = $invoice->payments()->sum('amount');
                    if ($totalPaid >= $invoice->total) {
                        $invoice->update(['status' => 'paid']);
                    }
                }
            }

            return $payment;
        });
    }
}
