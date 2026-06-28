<?php

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Community;
use App\Models\Financial\Invoice;
use App\Models\FinancialSetting;
use App\Models\Payment;
use App\Models\Unit;
use App\Services\Financial\DunningService;

beforeEach(function () {
    $this->community = Community::factory()->create();
    $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);
    $this->service = app(DunningService::class);
});

/**
 * Builds a FinancialSetting with dunning enabled and the given overrides.
 */
function makeDunningSetting(Community $community, array $overrides = []): FinancialSetting
{
    return FinancialSetting::factory()->create([
        'community_id' => $community->id,
        'dunning_policies' => array_replace_recursive([
            'enabled' => true,
            'grace_period_days' => 0,
            'restrictions' => [
                'restrict_ecosystem' => true,
                'restrict_pqrs' => true,
                'restrict_moving_permits' => true,
                'restrict_amenities' => false,
            ],
        ], $overrides),
    ]);
}

it('returns false when dunning is disabled globally', function () {
    $setting = FinancialSetting::factory()->create([
        'community_id' => $this->community->id,
        'dunning_policies' => ['enabled' => false, 'grace_period_days' => 0, 'restrictions' => []],
    ]);

    Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->subDays(30),
    ]);

    expect($this->service->isUnitInArrears($this->unit, $setting))->toBeFalse();
});

it('returns false when unit has no past-due invoices', function () {
    $setting = makeDunningSetting($this->community);

    Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->addDays(10),
    ]);

    expect($this->service->isUnitInArrears($this->unit, $setting))->toBeFalse();
});

it('returns true when unit has a past-due invoice and grace period is zero', function () {
    $setting = makeDunningSetting($this->community, ['grace_period_days' => 0]);

    Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->subDay(),
    ]);

    expect($this->service->isUnitInArrears($this->unit, $setting))->toBeTrue();
});

it('respects the grace period and does not restrict within grace window', function () {
    $setting = makeDunningSetting($this->community, ['grace_period_days' => 10]);

    Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->subDays(5),
    ]);

    expect($this->service->isUnitInArrears($this->unit, $setting))->toBeFalse();
});

it('restricts after the grace period expires', function () {
    $setting = makeDunningSetting($this->community, ['grace_period_days' => 10]);

    Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->subDays(15),
    ]);

    expect($this->service->isUnitInArrears($this->unit, $setting))->toBeTrue();
});

it('does not restrict paid invoices', function () {
    $setting = makeDunningSetting($this->community);

    Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PAID,
        'due_date' => now()->subDays(30),
    ]);

    expect($this->service->isUnitInArrears($this->unit, $setting))->toBeFalse();
});

it('returns restricted modules when unit is in arrears', function () {
    $setting = makeDunningSetting($this->community, [
        'restrictions' => [
            'restrict_ecosystem' => true,
            'restrict_pqrs' => false,
            'restrict_moving_permits' => true,
        ],
    ]);

    Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->subDay(),
    ]);

    $restricted = $this->service->getRestrictedModules($this->unit, $setting);

    expect($restricted)->toContain('marketplace')
        ->and($restricted)->not->toContain('pqrs')
        ->and($restricted)->toContain('resident_moves');
});

it('returns empty restricted modules when unit is not in arrears', function () {
    $setting = makeDunningSetting($this->community);

    expect($this->service->getRestrictedModules($this->unit, $setting))->toBeEmpty();
});

it('getRestrictionContext returns correct shape and amounts', function () {
    $setting = makeDunningSetting($this->community, ['grace_period_days' => 0]);

    Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->subDays(3),
        'total' => 250000,
        'subtotal' => 250000,
    ]);

    $context = $this->service->getRestrictionContext($this->unit, $setting);

    expect($context['is_restricted'])->toBeTrue()
        ->and($context['total_overdue'])->toBe(250000.0)
        ->and($context['oldest_due_date'])->not->toBeNull()
        ->and($context['restricted_modules'])->not->toBeEmpty();
});

it('isModuleRestricted returns false for non-restricted module', function () {
    $setting = makeDunningSetting($this->community, [
        'restrictions' => [
            'restrict_ecosystem' => true,
            'restrict_pqrs' => false,
        ],
    ]);

    Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->subDay(),
    ]);

    expect($this->service->isModuleRestricted($this->unit, 'pqrs', $setting))->toBeFalse();
    expect($this->service->isModuleRestricted($this->unit, 'marketplace', $setting))->toBeTrue();
});

// ─────────────────────────────────────────────────────────────────────────────
// Partial Payment Tests (outstanding_balance accessor + getRestrictionContext)
// ─────────────────────────────────────────────────────────────────────────────

it('outstanding_balance deducts confirmed payments from invoice total', function () {
    $invoice = Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->addDays(5),
        'total' => 500000,
        'subtotal' => 500000,
    ]);

    // Confirmed partial payment of 150,000
    Payment::create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'invoice_id' => $invoice->id,
        'status' => PaymentStatus::CONFIRMED,
        'method' => 'bank_transfer',
        'amount' => 150000,
    ]);

    // PENDING payment must NOT reduce the outstanding balance
    Payment::create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'invoice_id' => $invoice->id,
        'status' => PaymentStatus::PENDING,
        'method' => 'bank_transfer',
        'amount' => 50000,
    ]);

    $invoice->refresh();

    expect($invoice->outstanding_balance)->toBe(350000.0);
});

it('outstanding_balance is zero when invoice is fully paid', function () {
    $invoice = Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->addDays(5),
        'total' => 300000,
        'subtotal' => 300000,
    ]);

    Payment::create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'invoice_id' => $invoice->id,
        'status' => PaymentStatus::CONFIRMED,
        'method' => 'bank_transfer',
        'amount' => 300000,
    ]);

    $invoice->refresh();

    expect($invoice->outstanding_balance)->toBe(0.0);
});

it('getRestrictionContext total_overdue reflects partial payments not the gross total', function () {
    $setting = makeDunningSetting($this->community, ['grace_period_days' => 0]);

    $invoice = Invoice::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => InvoiceStatus::PENDING,
        'due_date' => now()->subDays(5),
        'total' => 1000000,
        'subtotal' => 1000000,
    ]);

    // Partial confirmed payment of 150,000
    Payment::create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'invoice_id' => $invoice->id,
        'status' => PaymentStatus::CONFIRMED,
        'method' => 'bank_transfer',
        'amount' => 150000,
    ]);

    $context = $this->service->getRestrictionContext($this->unit, $setting);

    // Should report 850,000 owed — NOT the gross 1,000,000
    expect($context['total_overdue'])->toBe(850000.0);
});
