<?php

use App\Enums\CommunityRole;
use App\Enums\LedgerEntryType;
use App\Models\Community;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('calculates the correct account statement balance from the ledger', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    // Attach user as a resident of the unit
    Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'user_id' => $user->id,
    ]);

    // Create a $1000 charge
    LedgerEntry::create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'type' => LedgerEntryType::CHARGE,
        'amount' => 1000,
        'description' => 'Test Charge',
    ]);

    // Create a -$400 payment
    LedgerEntry::create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'type' => LedgerEntryType::PAYMENT,
        'amount' => -400,
        'description' => 'Test Payment',
    ]);

    // Create a $50 platform commission (unit_id should be null based on our fix, but let's test isolation if it was there? No, we fixed the creation logic, but let's mock another charge)
    LedgerEntry::create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'type' => LedgerEntryType::ADJUSTMENT,
        'amount' => 200,
        'description' => 'Test Adjustment',
    ]);

    $url = route('api.finance.units.statement', [
        'community_slug' => $community->slug,
        'unit' => $unit->id,
    ]);

    $response = actingAs($user, 'sanctum')->getJson($url);

    $response->assertStatus(200)
        ->assertJsonPath('data.balance', 800) // 1000 - 400 + 200 = 800
        ->assertJsonPath('data.unit_id', $unit->id)
        ->assertJsonPath('data.summary.total_charges', 1200)
        ->assertJsonPath('data.summary.total_payments', 400);
});

it('denies access if user is not a resident of the unit', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $community->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    $url = route('api.finance.units.statement', [
        'community_slug' => $community->slug,
        'unit' => $unit->id,
    ]);

    $response = actingAs($user, 'sanctum')->getJson($url);
    $response->assertStatus(403);
});

it('denies access if unit belongs to another community', function () {
    $user = User::factory()->create();
    $community1 = Community::factory()->create(['status' => 'active']);
    $community1->users()->attach($user->id, ['role' => CommunityRole::Resident->value]);

    $community2 = Community::factory()->create(['status' => 'active']);
    $unit = Unit::factory()->create(['community_id' => $community2->id]);

    Resident::factory()->create([
        'community_id' => $community2->id,
        'unit_id' => $unit->id,
        'user_id' => $user->id,
    ]);

    $url = route('api.finance.units.statement', [
        'community_slug' => $community1->slug,
        'unit' => $unit->id,
    ]);

    $response = actingAs($user, 'sanctum')->getJson($url);
    $response->assertStatus(404);
});
