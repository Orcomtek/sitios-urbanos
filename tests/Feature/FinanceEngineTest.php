<?php

use App\Actions\Finance\CreateInvoiceAction;
use App\Actions\Finance\RegisterPaymentAction;
use App\Enums\InvoiceStatus;
use App\Enums\LedgerEntryType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Community;
use App\Models\LedgerEntry;
use App\Models\Resident;
use App\Models\Unit;
use App\Services\TenantContext;

beforeEach(function () {
    $this->community = Community::factory()->create();
    $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);
    $this->resident = Resident::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
    ]);

    $this->tenantContext = app(TenantContext::class);
    $this->tenantContext->set($this->community);
});

it('creates an invoice and ledger entry charge', function () {
    $action = new CreateInvoiceAction($this->tenantContext);

    $invoice = $action->execute([
        'unit_id' => $this->unit->id,
        'user_id' => $this->resident->user_id,
        'invoice_number' => 'INV-000001',
        'issue_date' => now(),
        'due_date' => now()->addDays(5),
        'subtotal' => 150000,
        'total' => 150000,
        'billing_period' => now()->format('Y-m'),
    ]);

    expect($invoice->id)->not->toBeNull()
        ->and($invoice->status)->toBe(InvoiceStatus::PENDING)
        ->and((int) $invoice->total)->toBe(150000);

    $ledger = LedgerEntry::where('invoice_id', $invoice->id)->first();

    expect($ledger)->not->toBeNull()
        ->and($ledger->type)->toBe(LedgerEntryType::CHARGE)
        ->and($ledger->amount)->toBe(150000);
});

it('registers a payment successfully updating invoice and creating ledger applied payment', function () {
    $createInvoice = new CreateInvoiceAction($this->tenantContext);

    $invoice = $createInvoice->execute([
        'unit_id' => $this->unit->id,
        'user_id' => $this->resident->user_id,
        'invoice_number' => 'INV-000002',
        'issue_date' => now(),
        'due_date' => now()->addDays(5),
        'subtotal' => 150000,
        'total' => 150000,
        'billing_period' => now()->format('Y-m'),
    ]);

    $action = new RegisterPaymentAction($this->tenantContext);

    $payment = $action->execute([
        'invoice_id' => $invoice->id,
        'method' => PaymentMethod::BANK_TRANSFER,
        'amount' => 150000,
        'idempotency_key' => 'unique-ref-123',
    ]);

    expect($payment->status)->toBe(PaymentStatus::CONFIRMED)
        ->and($payment->amount)->toBe(150000);

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::PAID);

    $ledger = LedgerEntry::where('payment_id', $payment->id)->first();

    expect($ledger)->not->toBeNull()
        ->and($ledger->type)->toBe(LedgerEntryType::PAYMENT)
        ->and($ledger->amount)->toBe(-150000);
});

it('prevents double registration through idempotency key', function () {
    $action = new RegisterPaymentAction($this->tenantContext);

    $payment1 = $action->execute([
        'unit_id' => $this->unit->id,
        'method' => PaymentMethod::CASH,
        'amount' => 50000,
        'idempotency_key' => 'idemp-123',
    ]);

    $payment2 = $action->execute([
        'unit_id' => $this->unit->id,
        'method' => PaymentMethod::CASH,
        'amount' => 50000,
        'idempotency_key' => 'idemp-123',
    ]);

    expect($payment1->id)->toBe($payment2->id);

    $ledgerCount = LedgerEntry::where('payment_id', $payment1->id)->count();
    expect($ledgerCount)->toBe(1);
});

it('prevents ledger updates or deletes', function () {
    $createInvoice = new CreateInvoiceAction($this->tenantContext);

    $invoice = $createInvoice->execute([
        'unit_id' => $this->unit->id,
        'invoice_number' => 'INV-000003',
        'issue_date' => now(),
        'due_date' => now(),
        'subtotal' => 10000,
        'total' => 10000,
        'billing_period' => now()->format('Y-m'),
    ]);

    $ledger = LedgerEntry::where('invoice_id', $invoice->id)->first();

    expect(fn () => $ledger->update(['amount' => 50000]))->toThrow(RuntimeException::class);
    expect(fn () => $ledger->delete())->toThrow(RuntimeException::class);
});
