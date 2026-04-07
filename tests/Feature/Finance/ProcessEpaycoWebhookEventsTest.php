<?php

use App\Actions\Finance\ProcessEpaycoWebhookAction;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\Finance\PaymentConfirmed;
use App\Events\Finance\PaymentFailed;
use App\Models\Community;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Unit;
use Illuminate\Support\Facades\Event;

it('dispatches PaymentConfirmed event on successful webhook payload', function () {
    Event::fake();

    $community = Community::factory()->create();
    $unit = Unit::factory()->create(['community_id' => $community->id]);
    $invoice = Invoice::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'status' => InvoiceStatus::PENDING,
        'amount' => 50000,
        'type' => InvoiceType::ADMIN_FEE,
        'issued_at' => now(),
        'due_date' => now()->addDays(5),
    ]);

    $payment = Payment::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'invoice_id' => $invoice->id,
        'amount' => 50000,
        'status' => PaymentStatus::PENDING,
        'method' => PaymentMethod::INTERNAL_EPAYCO,
        'idempotency_key' => 'test-idempotency-key',
    ]);

    $action = new ProcessEpaycoWebhookAction;

    $p_cust_id_cliente = config('finance.epayco.p_cust_id_cliente', 'testing');
    $p_key = config('finance.epayco.p_key', 'testing');
    $x_ref_payco = 'ext-ref-123';
    $x_transaction_id = 'test-trx-123';
    $x_amount = '50000';
    $x_currency_code = 'COP';

    $signatureStr = "{$p_cust_id_cliente}^{$p_key}^{$x_ref_payco}^{$x_transaction_id}^{$x_amount}^{$x_currency_code}";
    $signature = hash('md5', $signatureStr);

    config(['finance.epayco.p_cust_id_cliente' => $p_cust_id_cliente]);
    config(['finance.epayco.p_key' => $p_key]);

    $payload = [
        'x_ref_payco' => $x_ref_payco,
        'x_id_invoice' => $payment->idempotency_key,
        'x_amount' => $x_amount,
        'x_response' => 'Aceptada',
        'x_transaction_id' => $x_transaction_id,
        'x_currency_code' => $x_currency_code,
        'x_signature' => $signature,
    ];

    $action->execute($payload);

    Event::assertDispatched(PaymentConfirmed::class, function ($event) use ($payment) {
        return $event->payment->id === $payment->id;
    });

    Event::assertNotDispatched(PaymentFailed::class);
});

it('dispatches PaymentFailed event on rejected webhook payload', function () {
    Event::fake();

    $community = Community::factory()->create();
    $unit = Unit::factory()->create(['community_id' => $community->id]);
    $invoice = Invoice::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'status' => InvoiceStatus::PENDING,
        'amount' => 50000,
        'type' => InvoiceType::ADMIN_FEE,
        'issued_at' => now(),
        'due_date' => now()->addDays(5),
    ]);

    $payment = Payment::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'invoice_id' => $invoice->id,
        'amount' => 50000,
        'status' => PaymentStatus::PENDING,
        'method' => PaymentMethod::INTERNAL_EPAYCO,
        'idempotency_key' => 'test-idempotency-key-2',
    ]);

    $action = new ProcessEpaycoWebhookAction;

    $p_cust_id_cliente = config('finance.epayco.p_cust_id_cliente', 'testing');
    $p_key = config('finance.epayco.p_key', 'testing');
    $x_ref_payco = 'ext-ref-1234';
    $x_transaction_id = 'test-trx-1234';
    $x_amount = '50000';
    $x_currency_code = 'COP';

    $signatureStr = "{$p_cust_id_cliente}^{$p_key}^{$x_ref_payco}^{$x_transaction_id}^{$x_amount}^{$x_currency_code}";
    $signature = hash('md5', $signatureStr);

    config(['finance.epayco.p_cust_id_cliente' => $p_cust_id_cliente]);
    config(['finance.epayco.p_key' => $p_key]);

    $payload = [
        'x_ref_payco' => $x_ref_payco,
        'x_id_invoice' => $payment->idempotency_key,
        'x_amount' => $x_amount,
        'x_response' => 'Rechazada',
        'x_transaction_id' => $x_transaction_id,
        'x_currency_code' => $x_currency_code,
        'x_signature' => $signature,
    ];

    $action->execute($payload);

    Event::assertDispatched(PaymentFailed::class, function ($event) use ($payment) {
        return $event->payment->id === $payment->id;
    });

    Event::assertNotDispatched(PaymentConfirmed::class);
});
