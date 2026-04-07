<?php

use App\Enums\CommunityRole;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Community;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Unit;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('can fetch invoice state via tenant API', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

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

    $url = route('api.finance.invoice', [
        'community_slug' => $community->slug,
        'invoice' => $invoice->id,
    ]);

    $response = actingAs($user, 'sanctum')
        ->getJson($url);

    $response->assertStatus(200)
        ->assertJson([
            'id' => $invoice->id,
            'status' => InvoiceStatus::PENDING->value,
            'amount' => 50000,
            'due_date' => now()->addDays(5)->toDateString(),
        ]);
});

it('can fetch payment state via tenant API', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $unit = Unit::factory()->create(['community_id' => $community->id]);
    $invoice = Invoice::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
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
    ]);

    $url = route('api.finance.payment', [
        'community_slug' => $community->slug,
        'payment' => $payment->id,
    ]);

    actingAs($user, 'sanctum')
        ->getJson($url)
        ->assertStatus(200)
        ->assertJson([
            'id' => $payment->id,
            'status' => PaymentStatus::PENDING->value,
            'amount' => 50000,
        ]);
});
