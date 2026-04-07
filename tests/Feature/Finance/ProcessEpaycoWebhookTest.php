<?php

use App\Enums\InvoiceStatus;
use App\Enums\LedgerEntryType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Community;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Unit;

use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->community = Community::factory()->create();
    $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);
    $this->invoice = Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'amount' => 100000,
        'status' => InvoiceStatus::PENDING,
        'issued_at' => now(),
        'due_date' => now()->addDays(15),
    ]);

    $this->payment = Payment::create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'invoice_id' => $this->invoice->id,
        'amount' => 100000,
        'platform_commission' => 1500,
        'net_amount' => 98500,
        'method' => PaymentMethod::INTERNAL_EPAYCO,
        'status' => PaymentStatus::PENDING,
        'idempotency_key' => 'intent_123',
    ]);

    config(['finance.epayco.p_cust_id_cliente' => '1234']);
    config(['finance.epayco.p_key' => 'secret_key']);
});

it('processes a valid epayco webhook and updates ledger', function () {
    // Generate signature
    $p_cust_id_cliente = '1234';
    $p_key = 'secret_key';
    $x_ref_payco = 'epayco_ref_890';
    $x_transaction_id = 'tx_123';
    $x_amount = '100000';
    $x_currency_code = 'COP';
    $signature = hash('md5', "{$p_cust_id_cliente}^{$p_key}^{$x_ref_payco}^{$x_transaction_id}^{$x_amount}^{$x_currency_code}");

    $payload = [
        'x_ref_payco' => $x_ref_payco,
        'x_transaction_id' => $x_transaction_id,
        'x_amount' => $x_amount,
        'x_currency_code' => $x_currency_code,
        'x_signature' => $signature,
        'x_id_invoice' => $this->payment->idempotency_key,
        'x_response' => 'Aceptada',
    ];

    $response = postJson('/api/webhooks/epayco', $payload);

    $response->assertStatus(200);

    $this->payment->refresh();
    expect($this->payment->status)->toBe(PaymentStatus::CONFIRMED)
        ->and($this->payment->gateway_status)->toBe('Aceptada')
        ->and($this->payment->external_reference)->toBe('epayco_ref_890')
        ->and($this->payment->signature_verified)->toBeTrue()
        ->and($this->payment->paid_at)->not->toBeNull();

    $this->invoice->refresh();
    expect($this->invoice->status)->toBe(InvoiceStatus::PAID);

    $this->assertDatabaseHas('ledger_entries', [
        'payment_id' => $this->payment->id,
        'type' => LedgerEntryType::PAYMENT->value,
        'amount' => -100000,
    ]);

    $this->assertDatabaseHas('ledger_entries', [
        'payment_id' => $this->payment->id,
        'type' => LedgerEntryType::PLATFORM_COMMISSION->value,
        'amount' => 1500,
    ]);
});

it('rejects invalid signature', function () {
    $payload = [
        'x_ref_payco' => 'epayco_ref_890',
        'x_transaction_id' => 'tx_123',
        'x_amount' => '100000',
        'x_currency_code' => 'COP',
        'x_signature' => 'invalid_signature_md5',
        'x_id_invoice' => $this->payment->id,
        'x_response' => 'Aceptada',
    ];

    $response = postJson('/api/webhooks/epayco', $payload);
    $response->assertStatus(400);
});

it('is idempotent for duplicate accepted webhooks', function () {
    $this->payment->update([
        'status' => PaymentStatus::CONFIRMED,
        'external_reference' => 'epayco_ref_890',
        'gateway_status' => 'Aceptada',
    ]);

    $signature = hash('md5', '1234^secret_key^epayco_ref_890^tx_123^100000^COP');

    $payload = [
        'x_ref_payco' => 'epayco_ref_890',
        'x_transaction_id' => 'tx_123',
        'x_amount' => '100000',
        'x_currency_code' => 'COP',
        'x_signature' => $signature,
        'x_id_invoice' => $this->payment->id,
        'x_response' => 'Aceptada',
    ];

    $response = postJson('/api/webhooks/epayco', $payload);
    $response->assertStatus(200); // the endpoint still returns 200 to acknowledge receipt

    // Ensure Ledger entries aren't duplicated (should be 0 because we didn't create them manually yet and the webhook shouldn't either)
    $this->assertDatabaseCount('ledger_entries', 0);
});

it('fails validation on amount mismatch', function () {
    $signature = hash('md5', '1234^secret_key^epayco_ref_890^tx_123^999000^COP');

    $payload = [
        'x_ref_payco' => 'epayco_ref_890',
        'x_transaction_id' => 'tx_123',
        'x_amount' => '999000',
        'x_currency_code' => 'COP',
        'x_signature' => $signature,
        'x_id_invoice' => $this->payment->id,
        'x_response' => 'Aceptada',
    ];

    $response = postJson('/api/webhooks/epayco', $payload);
    $response->assertStatus(400); // Valid signature, but mismatched amount
});
