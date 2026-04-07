<?php

namespace App\Actions\Finance;

use App\Enums\InvoiceStatus;
use App\Enums\LedgerEntryType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class RegisterPaymentAction
{
    public function __construct(private TenantContext $tenantContext) {}

    public function execute(array $data): Payment
    {
        $communityId = $this->tenantContext->require()->id;

        if (! empty($data['idempotency_key'])) {
            $existing = Payment::where('community_id', $communityId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($data, $communityId) {
            $invoiceId = $data['invoice_id'] ?? null;
            $unitId = null;

            if ($invoiceId) {
                // Fails cleanly if invoice does not belong to active tenant
                $invoice = Invoice::where('community_id', $communityId)->findOrFail($invoiceId);
                $unitId = $invoice->unit_id;
            } else {
                $unitId = $data['unit_id'] ?? null;
            }

            $isInternal = $data['method'] === PaymentMethod::INTERNAL_EPAYCO->value || $data['method'] === PaymentMethod::INTERNAL_EPAYCO;

            if ($isInternal) {
                $commission = $data['platform_commission'] ?? 0;
                $netAmount = $data['amount'] - $commission;
            } else {
                $commission = 0;
                $netAmount = $data['amount'];
            }

            $payment = Payment::create([
                'community_id' => $communityId,
                'unit_id' => $unitId,
                'invoice_id' => $invoiceId,
                'method' => $data['method'],
                'amount' => $data['amount'],
                'platform_commission' => $commission,
                'net_amount' => $netAmount,
                'external_reference' => $data['external_reference'] ?? null,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'status' => PaymentStatus::CONFIRMED,
                'paid_at' => now(),
            ]);

            // Ledger entry for the principal payment
            LedgerEntry::create([
                'community_id' => $communityId,
                'unit_id' => $unitId,
                'payment_id' => $payment->id,
                'invoice_id' => $invoiceId,
                'type' => LedgerEntryType::PAYMENT,
                'amount' => -abs($payment->amount),
                'description' => 'Payment registered: '.$payment->id,
            ]);

            if ($commission > 0) {
                LedgerEntry::create([
                    'community_id' => $communityId,
                    'unit_id' => null,
                    'payment_id' => $payment->id,
                    'type' => LedgerEntryType::PLATFORM_COMMISSION,
                    'amount' => $commission,
                    'description' => 'Platform commission for payment: '.$payment->id,
                ]);
            }

            if ($invoiceId && isset($invoice)) {
                $invoice->update(['status' => InvoiceStatus::PAID]);
            }

            return $payment;
        });
    }
}
