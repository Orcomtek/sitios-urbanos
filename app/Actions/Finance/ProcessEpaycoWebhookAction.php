<?php

namespace App\Actions\Finance;

use App\Enums\InvoiceStatus;
use App\Enums\LedgerEntryType;
use App\Enums\PaymentStatus;
use App\Events\Finance\PaymentConfirmed;
use App\Events\Finance\PaymentFailed;
use App\Models\LedgerEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ProcessEpaycoWebhookAction
{
    public function execute(array $payload): void
    {
        // 1. Signature Validation
        $this->validateSignature($payload);

        $externalReference = $payload['x_ref_payco'] ?? null;
        $internalReference = $payload['x_id_invoice'] ?? null; // e.g. our payment idempotency_key or id
        $expectedAmount = (float) ($payload['x_amount'] ?? 0);
        $gatewayStatus = $payload['x_response'] ?? ''; // 'Aceptada', 'Rechazada', 'Fallida' etc

        if (! $externalReference || ! $internalReference) {
            throw new InvalidArgumentException('Missing reference payload parameters.');
        }

        // 2. Identify existing Payment (using idempotency_key or id depending on how it was sent)
        // We assume x_id_invoice holds the payment `idempotency_key` or `id`.
        $payment = Payment::where('id', $internalReference)
            ->orWhere('idempotency_key', $internalReference)
            ->first();

        if (! $payment) {
            throw new InvalidArgumentException("Payment not found for internal reference: $internalReference");
        }

        // 3. Webhook Duplicate Protection (Idempotency relying on external_reference & status)
        if ($payment->external_reference === $externalReference && $payment->status !== PaymentStatus::PENDING) {
            // Already processed this exact provider reference
            Log::info('Epayco webhook duplicate caught securely', ['external_reference' => $externalReference]);

            return;
        }

        // 4. Expected Amount Validation
        if ((float) $payment->amount !== $expectedAmount) {
            throw new InvalidArgumentException("Amount mismatch. Expected: {$payment->amount}, Received: {$expectedAmount}");
        }

        // 5. Existing Invoice Consistency Validation
        $invoice = $payment->invoice;
        if (! $invoice) {
            throw new InvalidArgumentException('Invoice consistency validation failed. No invoice associated.');
        }

        // 6. Processable status validation
        if ($payment->status !== PaymentStatus::PENDING) {
            Log::warning('Payment not in processable status', ['status' => $payment->status->value]);

            return;
        }

        // Map status
        $status = $this->mapGatewayStatus($gatewayStatus);

        DB::transaction(function () use ($payment, $invoice, $externalReference, $gatewayStatus, $payload, $status) {
            // Update payment intent
            $payment->update([
                'external_reference' => $externalReference,
                'gateway_status' => $gatewayStatus,
                'gateway_payload' => $payload,
                'signature_verified' => true,
                'status' => $status,
                'paid_at' => $status === PaymentStatus::CONFIRMED ? now() : null,
            ]);

            if ($status === PaymentStatus::CONFIRMED) {
                // Update Invoice
                if ($invoice->status !== InvoiceStatus::PAID) {
                    $invoice->update(['status' => InvoiceStatus::PAID]);
                }

                event(new PaymentConfirmed($payment));

                // Append Ledgers
                LedgerEntry::create([
                    'community_id' => $payment->community_id,
                    'unit_id' => $payment->unit_id,
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'type' => LedgerEntryType::PAYMENT,
                    'amount' => -abs($payment->amount),
                    'description' => 'Aggregator payment confirmed: '.$payment->id,
                ]);

                if ($payment->platform_commission > 0) {
                    LedgerEntry::create([
                        'community_id' => $payment->community_id,
                        'unit_id' => $payment->unit_id,
                        'payment_id' => $payment->id,
                        'type' => LedgerEntryType::PLATFORM_COMMISSION,
                        'amount' => $payment->platform_commission,
                        'description' => 'Platform commission derived safely from aggregator transaction: '.$payment->id,
                    ]);
                }
            }
        });

        // 7. Dispatch PaymentFailed outside transaction if it failed
        if ($status === PaymentStatus::FAILED) {
            event(new PaymentFailed($payment));
        }
    }

    private function validateSignature(array $payload): void
    {
        // ePayco signature format:
        // md5(p_cust_id_cliente . "^" . p_key . "^" . x_ref_payco . "^" . x_transaction_id . "^" . x_amount . "^" . x_currency_code)

        $p_cust_id_cliente = config('finance.epayco.p_cust_id_cliente');
        $p_key = config('finance.epayco.p_key');

        $x_ref_payco = $payload['x_ref_payco'] ?? '';
        $x_transaction_id = $payload['x_transaction_id'] ?? '';
        $x_amount = $payload['x_amount'] ?? '';
        $x_currency_code = $payload['x_currency_code'] ?? '';
        $rx_signature = $payload['x_signature'] ?? '';

        $stringToMD5 = "{$p_cust_id_cliente}^{$p_key}^{$x_ref_payco}^{$x_transaction_id}^{$x_amount}^{$x_currency_code}";
        $expectedSignature = hash('md5', $stringToMD5);

        if ($expectedSignature !== $rx_signature) {
            throw new InvalidArgumentException('Webhook signature validation failed.');
        }
    }

    private function mapGatewayStatus(string $gatewayStatus): PaymentStatus
    {
        $statusMap = [
            'Aceptada' => PaymentStatus::CONFIRMED,
            'Rechazada' => PaymentStatus::FAILED,
            'Fallida' => PaymentStatus::FAILED,
            // 'Pendiente' maps back to PENDING generally, but if webhook hits with Pendiente we could just return early
            'Pendiente' => PaymentStatus::PENDING,
        ];

        return $statusMap[$gatewayStatus] ?? PaymentStatus::FAILED;
    }
}
