<?php

use App\Models\Community;
use App\Models\Unit;
use App\Models\User;

it('requires authentication to view units', function () {
    $community = Community::factory()->create();

    $this->get(route('units.index', ['community_slug' => $community->slug]))
        ->assertRedirect(route('login'));
});

it('lists units for the active community', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $community->users()->attach($user, ['role' => 'admin']);

    $unit = Unit::factory()->create(['community_id' => $community->id]);
    $otherUnit = Unit::factory()->create(); // Belongs to a different community boundary

    $this->actingAs($user)
        ->get(route('units.index', ['community_slug' => $community->slug]))
        ->assertOk()
        ->assertSee($unit->identifier)
        ->assertDontSee($otherUnit->identifier);
});

it('can store a new unit with strict tenant binding', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $community->users()->attach($user, ['role' => 'admin']);

    $payload = [
        'identifier' => 'Torre A Apto 101',
        'type' => 'apartment',
        'status' => 'vacant',
    ];

    $this->actingAs($user)
        ->post(route('units.store', ['community_slug' => $community->slug]), $payload)
        ->assertRedirect(route('units.index', ['community_slug' => $community->slug]));

    $this->assertDatabaseHas('units', [
        'community_id' => $community->id,
        'identifier' => 'Torre A Apto 101',
        'type' => 'apartment',
    ]);
});

it('strictly validates identifier uniqueness per tenant', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $community->users()->attach($user, ['role' => 'admin']);

    // Seed existing
    Unit::factory()->create(['community_id' => $community->id, 'identifier' => '101']);

    $payload = [
        'identifier' => '101', // Duplicate in same community
        'type' => 'apartment',
        'status' => 'vacant',
    ];

    $this->actingAs($user)
        ->post(route('units.store', ['community_slug' => $community->slug]), $payload)
        ->assertSessionHasErrors(['identifier']);

    // Allow duplicate in different community
    $communityB = Community::factory()->create();
    $communityB->users()->attach($user, ['role' => 'admin']);

    $this->actingAs($user)
        ->post(route('units.store', ['community_slug' => $communityB->slug]), $payload)
        ->assertSessionHasNoErrors();
});

it('protects against updating units outside the tenant context', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $community->users()->attach($user, ['role' => 'admin']);

    $otherUnit = Unit::factory()->create(); // different community

    $payload = [
        'identifier' => 'Hacked',
        'type' => 'apartment',
        'status' => 'vacant',
    ];

    $this->actingAs($user)
        ->put(route('units.update', [
            'community_slug' => $community->slug,
            'unit' => $otherUnit->id,
        ]), $payload)
        ->assertStatus(404); // Bound by single unit fetch inside controller assert
});

it('can update a unit within the active community', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $community->users()->attach($user, ['role' => 'admin']);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    $payload = [
        'identifier' => 'Torre B Apto 202',
        'type' => 'apartment',
        'status' => 'occupied',
    ];

    $this->actingAs($user)
        ->put(route('units.update', [
            'community_slug' => $community->slug,
            'unit' => $unit->id,
        ]), $payload)
        ->assertRedirect(route('units.index', ['community_slug' => $community->slug]));

    $this->assertDatabaseHas('units', [
        'id' => $unit->id,
        'identifier' => 'Torre B Apto 202',
    ]);
});

it('can delete a unit securely', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $community->users()->attach($user, ['role' => 'admin']);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    $this->actingAs($user)
        ->delete(route('units.destroy', [
            'community_slug' => $community->slug,
            'unit' => $unit->id,
        ]))
        ->assertRedirect(route('units.index', ['community_slug' => $community->slug]));

    $this->assertSoftDeleted('units', [
        'id' => $unit->id,
    ]);
});
