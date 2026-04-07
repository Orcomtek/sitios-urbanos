<?php

use App\Models\Community;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use App\Models\Visitor;

beforeEach(function () {
    $this->community = Community::factory()->create();
    $this->centralDomain = config('app.central_domain', 'sitios-urbanos.test');
    $this->baseUrl = "http://{$this->community->slug}.{$this->centralDomain}";

    $this->residentUser = User::factory()->create();
    $this->community->users()->attach($this->residentUser, ['role' => 'resident']);

    $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);
    Resident::factory()->create([
        'unit_id' => $this->unit->id,
        'user_id' => $this->residentUser->id,
        'community_id' => $this->community->id,
    ]);

    $this->adminUser = User::factory()->create();
    $this->community->users()->attach($this->adminUser, ['role' => 'admin']);

    $this->guardUser = User::factory()->create();
    $this->community->users()->attach($this->guardUser, ['role' => 'guard']);

    // Create another community and user for isolation tests
    $this->otherCommunity = Community::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->otherCommunity->users()->attach($this->otherUser, ['role' => 'resident']);
});

it('allows residents to list their own visitors', function () {
    Visitor::factory()->count(3)->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
    ]);

    // Visitor for another unit
    $otherUnit = Unit::factory()->create(['community_id' => $this->community->id]);
    Visitor::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $otherUnit->id,
    ]);

    $response = $this->actingAs($this->residentUser)
        ->getJson("{$this->baseUrl}/api/security/visitors");

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');
});

it('allows admins to list all visitors in the community', function () {
    Visitor::factory()->count(4)->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
    ]);

    $response = $this->actingAs($this->adminUser)
        ->getJson("{$this->baseUrl}/api/security/visitors");

    $response->assertStatus(200);
    $response->assertJsonCount(4, 'data');
});

it('allows residents to create a visitor for their unit', function () {
    $response = $this->actingAs($this->residentUser)
        ->post("{$this->baseUrl}/api/security/visitors", [
            'unit_id' => $this->unit->id,
            'name' => 'John Doe',
            'type' => 'visitor',
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('visitors', [
        'name' => 'John Doe',
        'unit_id' => $this->unit->id,
        'created_by' => $this->residentUser->id,
        'status' => 'pending',
    ]);
});

it('prevents residents from creating visitors for other units', function () {
    $otherUnit = Unit::factory()->create(['community_id' => $this->community->id]);

    $response = $this->actingAs($this->residentUser)
        ->post("{$this->baseUrl}/api/security/visitors", [
            'unit_id' => $otherUnit->id,
            'name' => 'John Doe',
            'type' => 'visitor',
        ]);

    $response->assertStatus(403);
});

it('prevents cross-tenant access when creating visitors', function () {
    $otherUnit = Unit::factory()->create(['community_id' => $this->otherCommunity->id]);

    $response = $this->actingAs($this->residentUser)
        ->postJson("{$this->baseUrl}/api/security/visitors", [
            'unit_id' => $otherUnit->id,
            'name' => 'John Doe',
            'type' => 'visitor',
        ]);

    // Validation fails because the unit doesn't exist in the current tenant's scope
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['unit_id']);
});

it('allows guards to transition a visitor to entered and exited', function () {
    $visitor = Visitor::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => 'pending',
    ]);

    // Enter
    $response = $this->actingAs($this->guardUser)
        ->patchJson("{$this->baseUrl}/api/security/visitors/{$visitor->id}/enter");

    $response->assertStatus(200);
    expect($visitor->fresh()->status)->toBe('entered');
    expect($visitor->fresh()->entered_at)->not->toBeNull();

    // Exit
    $response2 = $this->actingAs($this->guardUser)
        ->patchJson("{$this->baseUrl}/api/security/visitors/{$visitor->id}/exit");

    $response2->assertStatus(200);
    expect($visitor->fresh()->status)->toBe('exited');
    expect($visitor->fresh()->exited_at)->not->toBeNull();
});

it('prevents residents from transitioning visitors', function () {
    $visitor = Visitor::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->residentUser)
        ->patchJson("{$this->baseUrl}/api/security/visitors/{$visitor->id}/enter");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);

    expect($visitor->fresh()->status)->toBe('pending');
});

it('prevents invalid state transitions', function () {
    $visitor = Visitor::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => 'pending',
    ]);

    // Cannot exit if pending
    $response = $this->actingAs($this->guardUser)
        ->patchJson("{$this->baseUrl}/api/security/visitors/{$visitor->id}/exit");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);

    // Change to entered
    $visitor->update(['status' => 'entered']);

    // Cannot enter if already entered
    $response2 = $this->actingAs($this->guardUser)
        ->patchJson("{$this->baseUrl}/api/security/visitors/{$visitor->id}/enter");

    $response2->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});
