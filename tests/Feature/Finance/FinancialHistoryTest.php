<?php

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('lists invoices for a unit', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'user_id' => $user->id,
    ]);

    Invoice::factory()->count(3)->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'amount' => 1000,
        'status' => \App\Enums\InvoiceStatus::PENDING,
        'type' => \App\Enums\InvoiceType::ADMIN_FEE,
        'issued_at' => now(),
        'due_date' => now()->addDays(5),
    ]);

    $url = route('api.finance.units.invoices', [
        'community_slug' => $community->slug,
        'unit' => $unit->id,
    ]);

    $response = actingAs($user, 'sanctum')->getJson($url);

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

it('lists payments for a unit', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'user_id' => $user->id,
    ]);

    Payment::factory()->count(2)->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'amount' => 500,
        'status' => \App\Enums\PaymentStatus::CONFIRMED,
        'method' => \App\Enums\PaymentMethod::INTERNAL_EPAYCO,
    ]);

    $url = route('api.finance.units.payments', [
        'community_slug' => $community->slug,
        'unit' => $unit->id,
    ]);

    $response = actingAs($user, 'sanctum')->getJson($url);

    $response->assertStatus(200);
    $this->assertCount(2, $response->json('data'));
});
