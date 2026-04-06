<?php

namespace Tests\Feature\Tenant;

use App\Models\Community;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;

it('can list residents in tenant context', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    $user->communities()->attach($community, ['role' => 'admin', 'unit_id' => null]);
    $unit = Unit::factory()->create(['community_id' => $community->id]);
    // Create residents with their own units so they don't violate the DB partial index
    Resident::factory()->count(3)->create(['community_id' => $community->id]);

    $response = $this->actingAs($user)->get(route('residents.index', ['community_slug' => $community->slug]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Tenant/Residents/Index')
        ->has('residents.data', 3)
    );
});

it('cannot see residents from another community', function () {
    $user = User::factory()->create();
    $community1 = Community::factory()->create();
    $community2 = Community::factory()->create();
    $user->communities()->attach($community1, ['role' => 'admin', 'unit_id' => null]);

    $unit2 = Unit::factory()->create(['community_id' => $community2->id]);
    Resident::factory()->create(['community_id' => $community2->id, 'unit_id' => $unit2->id]);

    $response = $this->actingAs($user)->get(route('residents.index', ['community_slug' => $community1->slug]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Tenant/Residents/Index')
        ->has('residents.data', 0)
    );
});

it('cannot assign resident to a unit from another community', function () {
    $user = User::factory()->create();
    $community1 = Community::factory()->create();
    $community2 = Community::factory()->create();
    $user->communities()->attach($community1, ['role' => 'admin', 'unit_id' => null]);

    $unit2 = Unit::factory()->create(['community_id' => $community2->id]);

    $response = $this->actingAs($user)->post(route('residents.store', ['community_slug' => $community1->slug]), [
        'unit_id' => $unit2->id,
        'full_name' => 'John Doe',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    $response->assertSessionHasErrors(['unit_id']);
});

it('can create resident', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    $user->communities()->attach($community, ['role' => 'admin', 'unit_id' => null]);
    $unit = Unit::factory()->create(['community_id' => $community->id]);

    $response = $this->actingAs($user)->post(route('residents.store', ['community_slug' => $community->slug]), [
        'unit_id' => $unit->id,
        'full_name' => 'John Doe',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    $response->assertRedirect(route('residents.index', ['community_slug' => $community->slug]));
    $this->assertDatabaseHas('residents', [
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'full_name' => 'John Doe',
    ]);
});

it('route bindings protect cross-tenant resident updates', function () {
    $user = User::factory()->create();
    $community1 = Community::factory()->create();
    $user->communities()->attach($community1, ['role' => 'admin', 'unit_id' => null]);

    $community2 = Community::factory()->create();
    $unit2 = Unit::factory()->create(['community_id' => $community2->id]);
    $resident2 = Resident::factory()->create(['community_id' => $community2->id, 'unit_id' => $unit2->id]);

    // Create a valid unit in community1 so validation passes, allowing us to test the controller's 404 protection
    $unit1 = Unit::factory()->create(['community_id' => $community1->id]);

    // Try to update $resident2 using $community1 context
    $response = $this->actingAs($user)->put(route('residents.update', ['community_slug' => $community1->slug, 'resident' => $resident2->id]), [
        'unit_id' => $unit1->id, // valid unit in community1
        'full_name' => 'Hacked Name',
        'resident_type' => 'tenant',
        'is_active' => true,
        'pays_administration' => false,
    ]);

    $response->assertStatus(404);
});
