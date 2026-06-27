<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Community;
use App\Models\Payment;
use App\Models\Unit;

use function Pest\Laravel\postJson;

/*
|--------------------------------------------------------------------------
| EpaycoWebhookController Tests
|--------------------------------------------------------------------------
| Tests for the Block 40.3 webhook endpoint at POST /webhooks/epayco.
| Uses SHA-256 signature validation via config('epayco.*').
*/

function buildEpaycoPayload(string $refPayco, string $transactionId, string $amount, string $state = 'Aceptada'): array
{
    $pCustId = config('epayco.p_cust_id_cliente');
    $pKey = config('epayco.p_key');
    $currencyCode = 'COP';

    $signatureString = "{$pCustId}^{$pKey}^{$refPayco}^{$transactionId}^{$amount}^{$currencyCode}";
    $signature = hash('sha256', $signatureString);

    return [
        'x_ref_payco' => $refPayco,
        'x_transaction_id' => $transactionId,
        'x_amount' => $amount,
        'x_currency_code' => $currencyCode,
        'x_signature' => $signature,
        'x_transaction_state' => $state,
    ];
}

beforeEach(function () {
    config([
        'epayco.p_cust_id_cliente' => 'test_cust_id',
        'epayco.p_key' => 'test_p_key',
    ]);

    $this->community = Community::factory()->create();
    $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);

    $this->payment = Payment::withoutGlobalScopes()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'amount' => 100000,
        'method' => PaymentMethod::INTERNAL_EPAYCO,
        'status' => PaymentStatus::PENDING,
        'external_reference' => 'ref_payco_001',
        'idempotency_key' => 'idem_001',
    ]);
});

it('reconciles a valid webhook to confirmed status', function () {
    $payload = buildEpaycoPayload('ref_payco_001', 'tx_001', '100000', 'Aceptada');

    $response = postJson(
        route('webhooks.epayco'),
        $payload,
    );

    $response->assertStatus(200)
        ->assertJson(['status' => 'ok']);

    $this->payment->refresh();

    expect($this->payment->status)->toBe(PaymentStatus::CONFIRMED)
        ->and($this->payment->gateway_status)->toBe('Aceptada')
        ->and($this->payment->signature_verified)->toBeTrue()
        ->and($this->payment->gateway_payload)->toBeArray()
        ->and($this->payment->paid_at)->not->toBeNull();
});

it('rejects an invalid signature with 401', function () {
    $payload = [
        'x_ref_payco' => 'ref_payco_001',
        'x_transaction_id' => 'tx_001',
        'x_amount' => '100000',
        'x_currency_code' => 'COP',
        'x_signature' => 'spoofed_signature_value',
        'x_transaction_state' => 'Aceptada',
    ];

    $response = postJson(
        route('webhooks.epayco'),
        $payload,
    );

    $response->assertStatus(401);

    $this->payment->refresh();
    expect($this->payment->status)->toBe(PaymentStatus::PENDING)
        ->and($this->payment->signature_verified)->toBeFalsy();
});

it('returns 404 when payment not found by external_reference', function () {
    $payload = buildEpaycoPayload('non_existent_ref', 'tx_002', '100000', 'Aceptada');

    $response = postJson(
        route('webhooks.epayco'),
        $payload,
    );

    $response->assertStatus(404);
});

it('handles idempotent duplicate webhooks gracefully', function () {
    // First: mark as already processed
    $this->payment->update([
        'status' => PaymentStatus::CONFIRMED,
        'gateway_status' => 'Aceptada',
        'signature_verified' => true,
        'paid_at' => now(),
    ]);

    $payload = buildEpaycoPayload('ref_payco_001', 'tx_001', '100000', 'Aceptada');

    $response = postJson(
        route('webhooks.epayco'),
        $payload,
    );

    $response->assertStatus(200)
        ->assertJson(['status' => 'already_processed']);
});

it('maps rejected state to failed status', function () {
    $payload = buildEpaycoPayload('ref_payco_001', 'tx_003', '100000', 'Rechazada');

    $response = postJson(
        route('webhooks.epayco'),
        $payload,
    );

    $response->assertStatus(200);

    $this->payment->refresh();
    expect($this->payment->status)->toBe(PaymentStatus::FAILED)
        ->and($this->payment->paid_at)->toBeNull();
});
