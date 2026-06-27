<?php

namespace App\Actions\Finance;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\SplitConfigurationException;
use App\Models\FinancialSetting;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Financial\SplitEngineService;
use App\Services\TenantContext;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreatePaymentAttemptAction
{
    public function __construct(
        private TenantContext $tenantContext,
        private SplitEngineService $splitEngine,
    ) {}

    /**
     * Create a payment intent for an invoice, computing the split distribution.
     *
     * @return array{payment: Payment, split: array<string, mixed>}
     *
     * @throws InvalidArgumentException
     * @throws SplitConfigurationException
     */
    public function execute(Invoice $invoice): array
    {
        $communityId = $this->tenantContext->require()->id;

        if ($invoice->community_id !== $communityId) {
            throw new InvalidArgumentException('Invoice does not belong to active community.');
        }

        if ($invoice->status !== InvoiceStatus::PENDING) {
            throw new InvalidArgumentException('Only pending invoices are payable.');
        }

        $existingPayment = Payment::where('invoice_id', $invoice->id)
            ->where('status', PaymentStatus::PENDING)
            ->latest()
            ->first();

        if ($existingPayment) {
            $amount = (int) round((float) $invoice->total);
            $settings = FinancialSetting::where('community_id', $communityId)->first();
            $split = $this->splitEngine->calculate($amount, $settings, $communityId);

            return ['payment' => $existingPayment, 'split' => $split];
        }

        $amount = (int) round((float) $invoice->total);

        $settings = FinancialSetting::where('community_id', $communityId)->first();
        $split = $this->splitEngine->calculate($amount, $settings, $communityId);

        $idempotencyKey = 'intent_'.Str::uuid()->toString();

        $payment = Payment::create([
            'community_id' => $communityId,
            'unit_id' => $invoice->unit_id,
            'invoice_id' => $invoice->id,
            'method' => PaymentMethod::INTERNAL_EPAYCO,
            'amount' => $amount,
            'platform_commission' => (int) $split['platform_commission'],
            'net_amount' => (int) $split['net_amount'],
            'idempotency_key' => $idempotencyKey,
            'status' => PaymentStatus::PENDING,
        ]);

        return ['payment' => $payment, 'split' => $split];
    }
}
