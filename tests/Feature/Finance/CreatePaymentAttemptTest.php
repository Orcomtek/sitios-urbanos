<?php

use App\Actions\Finance\CreatePaymentAttemptAction;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Community;
use App\Models\Invoice;
use App\Models\Unit;
use App\Services\TenantContext;

// uses(TestCase::class, RefreshDatabase::class);

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

    $this->tenantContext = app(TenantContext::class);
    $this->tenantContext->set($this->community);

    config(['finance.commission.type' => 'fixed']);
    config(['finance.commission.value' => 1500]);
});

it('creates a payment attempt with correct net amount', function () {
    $action = new CreatePaymentAttemptAction($this->tenantContext);

    $payment = $action->execute($this->invoice);

    expect($payment->community_id)->toBe($this->community->id)
        ->and($payment->invoice_id)->toBe($this->invoice->id)
        ->and($payment->amount)->toBe(100000)
        ->and($payment->platform_commission)->toBe(1500)
        ->and($payment->net_amount)->toBe(98500)
        ->and($payment->status)->toBe(PaymentStatus::PENDING)
        ->and($payment->idempotency_key)->toStartWith('intent_')
        ->and($payment->method)->toBe(PaymentMethod::INTERNAL_EPAYCO);
});

it('calculates percentage commission correctly', function () {
    config(['finance.commission.type' => 'percentage']);
    config(['finance.commission.value' => 2]); // 2%

    $action = new CreatePaymentAttemptAction($this->tenantContext);

    $payment = $action->execute($this->invoice);

    expect($payment->amount)->toBe(100000)
        ->and($payment->platform_commission)->toBe(2000) // 2% of 100000
        ->and($payment->net_amount)->toBe(98000);
});

it('throws exception if invoice is already paid', function () {
    $this->invoice->update(['status' => InvoiceStatus::PAID]);

    $action = new CreatePaymentAttemptAction($this->tenantContext);

    $action->execute($this->invoice);
})->throws(InvalidArgumentException::class, 'Only pending invoices are payable.');
