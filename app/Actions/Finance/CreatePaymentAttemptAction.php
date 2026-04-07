<?php

namespace App\Actions\Finance;

use App\Models\Payment;
use App\Models\Invoice;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Services\TenantContext;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreatePaymentAttemptAction
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    public function execute(Invoice $invoice): Payment
    {
        $communityId = $this->tenantContext->require()->id;

        if ($invoice->community_id !== $communityId) {
            throw new InvalidArgumentException("Invoice does not belong to active community.");
        }

        if ($invoice->status === \App\Enums\InvoiceStatus::PAID) {
            throw new InvalidArgumentException("Invoice is already paid.");
        }

        // Configuration-driven commission logic
        $commissionType = config('finance.commission.type', 'fixed');
        $commissionValue = config('finance.commission.value', 1500);

        $commission = 0;
        if ($commissionType === 'percentage') {
            $commission = (int) round($invoice->amount * ($commissionValue / 100));
        } else {
            $commission = (int) $commissionValue;
        }

        $netAmount = $invoice->amount - $commission;

        if ($netAmount < 0) {
            $netAmount = 0; // Prevent negative net amounts if commission is unusually high
        }

        $idempotencyKey = 'intent_' . Str::uuid()->toString();

        return Payment::create([
            'community_id' => $communityId,
            'unit_id' => $invoice->unit_id,
            'invoice_id' => $invoice->id,
            'method' => PaymentMethod::INTERNAL_EPAYCO,
            'amount' => $invoice->amount,
            'platform_commission' => $commission,
            'net_amount' => $netAmount,
            'idempotency_key' => $idempotencyKey,
            'status' => PaymentStatus::PENDING,
        ]);
    }
}
