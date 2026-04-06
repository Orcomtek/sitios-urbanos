<?php

use App\Models\Community;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use App\Services\TenantContext;

beforeEach(function () {
    $this->communityA = Community::factory()->create(['slug' => 'community-a']);
    $this->communityB = Community::factory()->create(['slug' => 'community-b']);

    $this->userA = User::factory()->create();
    $this->communityA->users()->attach($this->userA, ['role' => 'admin']);

    $this->userB = User::factory()->create();
    $this->communityB->users()->attach($this->userB, ['role' => 'admin']);

    $this->unitA = Unit::factory()->create([
        'community_id' => $this->communityA->id,
        'identifier' => '101A',
    ]);

    $this->unitB = Unit::factory()->create([
        'community_id' => $this->communityB->id,
        'identifier' => '101B',
    ]);
});

it('prevents querying models of other tenants when scoped', function () {
    // Act as user A in Community A
    app(TenantContext::class)->set($this->communityA);

    $units = Unit::all();

    expect($units)->toHaveCount(1)
        ->and($units->first()->id)->toBe($this->unitA->id)
        ->and(Unit::find($this->unitB->id))->toBeNull();
});

it('resists payload manipulation and assigns the correct tenant automatically on creation', function () {
    // Act as user A in Community A
    app(TenantContext::class)->set($this->communityA);

    $unit = Unit::create([
        'community_id' => $this->communityB->id, // Malicious attempt to inject community B
        'identifier' => '999X',
        'type' => 'apartment',
        'status' => 'occupied',
    ]);

    expect($unit->community_id)->toBe($this->communityA->id);

    // Also sanity check Resident creation
    $resident = Resident::create([
        'community_id' => $this->communityB->id,
        'unit_id' => $unit->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'type' => 'tenant',
        'status' => 'active',
    ]);

    expect($resident->community_id)->toBe($this->communityA->id);
});

it('denies cross-tenant route model binding access', function () {
    $this->actingAs($this->userA);

    // Try to edit Unit B (which belongs to Community B) while in Community A's subdomain runtime
    $url = route('units.edit', [
        'community_slug' => $this->communityA->slug,
        'unit' => $this->unitB->id,
    ]);

    $response = $this->get($url);

    // The global scope should prevent Route Model Binding from finding it, resulting in a 404
    $response->assertStatus(404);
});

it('allows normal route model binding access for own resources', function () {
    $this->actingAs($this->userA);

    $url = route('units.edit', [
        'community_slug' => $this->communityA->slug,
        'unit' => $this->unitA->id,
    ]);

    $response = $this->get($url);

    $response->assertStatus(200);
});

it('does not apply global scope when tenant context is empty', function () {
    // No context set
    $units = Unit::all();

    expect($units)->toHaveCount(2);
});
