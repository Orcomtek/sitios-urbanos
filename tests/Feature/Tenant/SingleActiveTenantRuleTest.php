<?php

namespace Tests\Feature\Tenant;

use App\Models\Community;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;

it('enforces single active tenant per unit physically via validation', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    $user->communities()->attach($community, ['role' => 'admin']);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    // Create a first active tenant
    Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'full_name' => 'First Active Tenant',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => true,
    ]);

    // Attempt to store a second active tenant for the same unit
    $response = $this->actingAs($user)->post(route('residents.store', ['community_slug' => $community->slug]), [
        'unit_id' => $unit->id,
        'full_name' => 'Second Active Tenant',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    // Should receive a validation error for 'is_active'
    $response->assertSessionHasErrors(['is_active']);

    // Attempt to store a second tenant but as inactive should PASS
    $response = $this->actingAs($user)->post(route('residents.store', ['community_slug' => $community->slug]), [
        'unit_id' => $unit->id,
        'full_name' => 'Inactive Tenant',
        'resident_type' => 'tenant',
        'is_active' => false,
        'pays_administration' => false,
    ]);

    $response->assertSessionHasNoErrors();

    // Attempt to store an active owner should PASS
    $response = $this->actingAs($user)->post(route('residents.store', ['community_slug' => $community->slug]), [
        'unit_id' => $unit->id,
        'full_name' => 'Active Owner',
        'resident_type' => 'owner',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    $response->assertSessionHasNoErrors();
});

it('enforces single active tenant during updates', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    $user->communities()->attach($community, ['role' => 'admin']);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    // Create a first active tenant
    Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'full_name' => 'First Active Tenant', // using direct DB create to skip requests
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => true,
    ]);

    // Create a second inactive tenant
    $resident2 = Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'full_name' => 'Second Inactive Tenant',
        'resident_type' => 'tenant',
        'is_active' => false,
        'pays_administration' => false,
    ]);

    // Attempt to update second tenant to active
    $response = $this->actingAs($user)->put(route('residents.update', ['community_slug' => $community->slug, 'resident' => $resident2->id]), [
        'unit_id' => $unit->id,
        'full_name' => 'Trying to activate',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    // Error
    $response->assertSessionHasErrors(['is_active']);

    // Attempt to update second tenant to active but for a NEW unit without active tenants should PASS
    $unit2 = Unit::factory()->create(['community_id' => $community->id]);

    $response = $this->actingAs($user)->put(route('residents.update', ['community_slug' => $community->slug, 'resident' => $resident2->id]), [
        'unit_id' => $unit2->id,
        'full_name' => 'Moving and activating',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    $response->assertSessionHasNoErrors();
});
