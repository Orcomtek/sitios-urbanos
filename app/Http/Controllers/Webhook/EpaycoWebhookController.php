<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Financial\EpaycoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EpaycoWebhookController extends Controller
{
    /**
     * ePayco transaction state to internal PaymentStatus mapping.
     *
     * @var array<string, PaymentStatus>
     */
    private const STATE_MAP = [
        'Aceptada' => PaymentStatus::CONFIRMED,
        'Rechazada' => PaymentStatus::FAILED,
        'Pendiente' => PaymentStatus::PENDING,
        'Fallida' => PaymentStatus::FAILED,
        'Reversada' => PaymentStatus::REFUNDED,
    ];

    public function handle(Request $request, EpaycoService $epaycoService): JsonResponse
    {
        $payload = $request->all();
        $refPayco = $payload['x_ref_payco'] ?? null;
        $transactionId = $payload['x_transaction_id'] ?? null;

        Log::info('[ePayco Webhook] Received notification', [
            'x_ref_payco' => $refPayco,
            'x_transaction_id' => $transactionId,
        ]);

        // --- Step 1: Cryptographic signature validation ---
        $generatedSignature = $epaycoService->generateSignature($payload);
        $incomingSignature = $payload['x_signature'] ?? '';

        if (! hash_equals($generatedSignature, $incomingSignature)) {
            Log::warning('[ePayco Webhook] Signature mismatch — possible spoofing attempt', [
                'x_ref_payco' => $refPayco,
                'x_transaction_id' => $transactionId,
                'expected' => $generatedSignature,
                'received' => $incomingSignature,
            ]);

            abort(401, 'Invalid signature');
        }

        // --- Step 2: Locate Payment (bypass TenantScoped global scope) ---
        $payment = Payment::withoutGlobalScopes()
            ->where('external_reference', $refPayco)
            ->first();

        if (! $payment) {
            Log::error('[ePayco Webhook] Payment not found for external_reference', [
                'x_ref_payco' => $refPayco,
                'x_transaction_id' => $transactionId,
            ]);

            abort(404, 'Payment not found');
        }

        // --- Step 3: Idempotency check ---
        $incomingState = $payload['x_transaction_state'] ?? 'Pendiente';
        $mappedStatus = self::STATE_MAP[$incomingState] ?? PaymentStatus::PENDING;

        if ($payment->signature_verified && $payment->gateway_status === $incomingState) {
            Log::info('[ePayco Webhook] Idempotent skip — already processed', [
                'x_ref_payco' => $refPayco,
                'payment_id' => $payment->id,
                'gateway_status' => $incomingState,
            ]);

            return response()->json(['status' => 'already_processed'], 200);
        }

        // --- Step 4: Reconcile Payment ---
        $payment->update([
            'gateway_payload' => $payload,
            'gateway_status' => $incomingState,
            'signature_verified' => true,
            'status' => $mappedStatus,
            'paid_at' => $mappedStatus === PaymentStatus::CONFIRMED ? now() : $payment->paid_at,
        ]);

        Log::info('[ePayco Webhook] Payment reconciled', [
            'payment_id' => $payment->id,
            'x_ref_payco' => $refPayco,
            'status' => $mappedStatus->value,
            'gateway_status' => $incomingState,
        ]);

        // Audit split metadata on confirmed payments
        if ($mappedStatus === PaymentStatus::CONFIRMED) {
            Log::info('[ePayco Webhook] Split audit trail', [
                'payment_id' => $payment->id,
                'platform_commission' => $payment->platform_commission,
                'net_amount' => $payment->net_amount,
                'community_id' => $payment->community_id,
                'split_payload' => collect($payload)->only([
                    'x_split_payment', 'x_split_type',
                    'x_split_primary_receiver', 'x_split_primary_receiver_fee',
                ])->toArray(),
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
