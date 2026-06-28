<?php

namespace Tests\Feature\Tenant;

use App\Models\Community;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;

it('auto-deactivates existing tenant when a new active tenant is created for same unit', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    $user->communities()->attach($community, ['role' => 'tenant_admin']);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    // Create a first active tenant
    $firstTenant = Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'full_name' => 'First Active Tenant',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => true,
    ]);

    // Store a second active tenant for the same unit — service auto-deactivates the first
    $response = $this->actingAs($user)->post(route('tenant.admin.core.residents.store', ['community_slug' => $community->slug]), [
        'unit_id' => $unit->id,
        'full_name' => 'Second Active Tenant',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    // First tenant should be auto-deactivated
    $firstTenant->refresh();
    expect($firstTenant->is_active)->toBeFalse();

    // Second tenant should be active
    $secondTenant = Resident::where('full_name', 'Second Active Tenant')->first();
    expect($secondTenant)->not->toBeNull()
        ->and($secondTenant->is_active)->toBeTrue();
});

it('creates owner without deactivating existing active tenant', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    $user->communities()->attach($community, ['role' => 'tenant_admin']);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    // Create a first active tenant
    $firstTenant = Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'full_name' => 'First Active Tenant',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => true,
    ]);

    // Store an owner — should NOT deactivate existing tenant
    $response = $this->actingAs($user)->post(route('tenant.admin.core.residents.store', ['community_slug' => $community->slug]), [
        'unit_id' => $unit->id,
        'full_name' => 'New Owner',
        'resident_type' => 'owner',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    $response->assertSessionHasNoErrors();

    // First tenant should still be active (owners don't deactivate tenants)
    $firstTenant->refresh();
    expect($firstTenant->is_active)->toBeTrue();
});

it('allows active owner creation alongside active tenant', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    $user->communities()->attach($community, ['role' => 'tenant_admin']);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'full_name' => 'Active Tenant',
        'resident_type' => 'tenant',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('tenant.admin.core.residents.store', ['community_slug' => $community->slug]), [
        'unit_id' => $unit->id,
        'full_name' => 'Active Owner',
        'resident_type' => 'owner',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    $response->assertSessionHasNoErrors();
});

it('auto-deactivates existing tenant when updating another tenant to active in same unit', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    $user->communities()->attach($community, ['role' => 'tenant_admin']);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    $firstTenant = Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'full_name' => 'First Active Tenant',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => true,
    ]);

    $secondTenant = Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'full_name' => 'Second Inactive Tenant',
        'resident_type' => 'tenant',
        'is_active' => false,
        'pays_administration' => false,
    ]);

    // Activate the second tenant — should auto-deactivate the first
    $response = $this->actingAs($user)->put(route('tenant.admin.core.residents.update', ['community_slug' => $community->slug, 'resident' => $secondTenant->id]), [
        'unit_id' => $unit->id,
        'full_name' => 'Trying to activate',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    $response->assertSessionHasNoErrors();

    $firstTenant->refresh();
    expect($firstTenant->is_active)->toBeFalse();

    $secondTenant->refresh();
    expect($secondTenant->is_active)->toBeTrue();
});
