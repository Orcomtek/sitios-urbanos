<?php

use App\Actions\Finance\CreatePaymentAttemptAction;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Community;
use App\Models\Financial\Invoice;
use App\Models\FinancialSetting;
use App\Models\Unit;
use App\Services\TenantContext;

beforeEach(function () {
    $this->community = Community::factory()->create();
    $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);
    $this->invoice = Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'total' => 100000,
        'subtotal' => 100000,
        'status' => InvoiceStatus::PENDING,
        'invoice_number' => 'INV-PA-001',
        'issue_date' => now(),
        'due_date' => now()->addDays(15),
        'billing_period' => now()->format('Y-m'),
    ]);

    $this->tenantContext = app(TenantContext::class);
    $this->tenantContext->set($this->community);

    // Create FinancialSetting with ePayco allied account for split engine
    FinancialSetting::create([
        'community_id' => $this->community->id,
        'epayco_allied_account_id' => 'test-allied-123',
        'commission_type' => 'fixed',
        'commission_value' => 1500,
    ]);
});

it('creates a payment attempt with correct net amount', function () {
    $action = app(CreatePaymentAttemptAction::class);

    $result = $action->execute($this->invoice);
    $payment = $result['payment'];

    expect($payment->community_id)->toBe($this->community->id)
        ->and($payment->invoice_id)->toBe($this->invoice->id)
        ->and((int) $payment->amount)->toBe(100000)
        ->and((int) $payment->platform_commission)->toBe(1500)
        ->and((int) $payment->net_amount)->toBe(98500)
        ->and($payment->status)->toBe(PaymentStatus::PENDING)
        ->and($payment->idempotency_key)->toStartWith('intent_')
        ->and($payment->method)->toBe(PaymentMethod::INTERNAL_EPAYCO);
});

it('calculates percentage commission correctly', function () {
    // Override to percentage commission (200 = 2% in hundredths notation)
    FinancialSetting::where('community_id', $this->community->id)->update([
        'commission_type' => 'percentage',
        'commission_value' => 200, // 200 hundredths = 2%
    ]);

    $action = app(CreatePaymentAttemptAction::class);

    $result = $action->execute($this->invoice);
    $payment = $result['payment'];

    expect((int) $payment->amount)->toBe(100000)
        ->and((int) $payment->platform_commission)->toBe(2000) // 2% of 100000
        ->and((int) $payment->net_amount)->toBe(98000);
});

it('throws exception if invoice is already paid', function () {
    $this->invoice->update(['status' => InvoiceStatus::PAID]);

    $action = app(CreatePaymentAttemptAction::class);

    $action->execute($this->invoice);
})->throws(InvalidArgumentException::class, 'Only pending invoices are payable.');
