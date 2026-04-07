<?php

use App\Enums\CommunityRole;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Community;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;

it('allows residents to initiate payment for a pending invoice', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $unit = Unit::factory()->create(['community_id' => $community->id]);
    Resident::factory()->create(['user_id' => $user->id, 'unit_id' => $unit->id, 'community_id' => $community->id]);

    $invoice = Invoice::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'status' => InvoiceStatus::PENDING,
        'amount' => 50000,
        'type' => InvoiceType::ADMIN_FEE,
        'issued_at' => now(),
        'due_date' => now()->addDays(5),
    ]);

    $url = route('api.finance.invoices.pay', [
        'community_slug' => $community->slug,
        'invoice' => $invoice->id,
    ]);

    $response = actingAs($user, 'sanctum')->postJson($url);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'invoice_id' => $invoice->id,
                'status' => PaymentStatus::PENDING->value,
                'amount' => 50000,
            ],
        ]);
});

it('reuses existing pending payment attempt for the same invoice', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $unit = Unit::factory()->create(['community_id' => $community->id]);
    Resident::factory()->create(['user_id' => $user->id, 'unit_id' => $unit->id, 'community_id' => $community->id]);

    $invoice = Invoice::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'status' => InvoiceStatus::PENDING,
        'amount' => 50000,
        'type' => InvoiceType::ADMIN_FEE,
        'issued_at' => now(),
        'due_date' => now()->addDays(5),
    ]);

    $existingPayment = Payment::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'invoice_id' => $invoice->id,
        'status' => PaymentStatus::PENDING,
        'method' => PaymentMethod::INTERNAL_EPAYCO,
        'amount' => 50000,
    ]);

    $url = route('api.finance.invoices.pay', [
        'community_slug' => $community->slug,
        'invoice' => $invoice->id,
    ]);

    $response = actingAs($user, 'sanctum')->postJson($url);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $existingPayment->id,
            ],
        ]);

    assertDatabaseCount('payments', 1);
});

it('rejects initiation if invoice is already paid', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $unit = Unit::factory()->create(['community_id' => $community->id]);
    Resident::factory()->create(['user_id' => $user->id, 'unit_id' => $unit->id, 'community_id' => $community->id]);

    $invoice = Invoice::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'status' => InvoiceStatus::PAID,
        'amount' => 50000,
        'type' => InvoiceType::ADMIN_FEE,
        'issued_at' => now(),
        'due_date' => now()->addDays(5),
    ]);

    $url = route('api.finance.invoices.pay', [
        'community_slug' => $community->slug,
        'invoice' => $invoice->id,
    ]);

    $response = actingAs($user, 'sanctum')->postJson($url);

    $response->assertStatus(422)
        ->assertJsonFragment([
            'message' => 'Only pending invoices are payable.',
        ]);
});

it('rejects unauthorized access outside tenant boundary', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $otherCommunity = Community::factory()->create(['status' => 'active']);

    $unit = Unit::factory()->create(['community_id' => $otherCommunity->id]);

    $invoice = Invoice::factory()->create([
        'community_id' => $otherCommunity->id,
        'unit_id' => $unit->id,
        'status' => InvoiceStatus::PENDING,
        'amount' => 50000,
        'type' => InvoiceType::ADMIN_FEE,
        'issued_at' => now(),
        'due_date' => now()->addDays(5),
    ]);

    $url = route('api.finance.invoices.pay', [
        'community_slug' => $community->slug, // Using current community slug
        'invoice' => $invoice->id, // But invoice belongs to another community
    ]);

    actingAs($user, 'sanctum')->postJson($url)->assertStatus(404);
});
