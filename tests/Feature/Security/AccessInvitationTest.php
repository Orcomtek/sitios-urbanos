<?php

use App\Models\AccessInvitation;
use App\Models\Community;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;

beforeEach(function () {
    $this->community = Community::factory()->create([
        'slug' => 'test-community',
    ]);

    $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);

    $this->admin = User::factory()->create();
    $this->admin->communities()->attach($this->community->id, ['role' => 'admin']);

    $this->guard = User::factory()->create();
    $this->guard->communities()->attach($this->community->id, ['role' => 'guard']);

    $this->residentUser = User::factory()->create();
    $this->residentUser->communities()->attach($this->community->id, ['role' => 'resident']);

    $this->resident = Resident::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'user_id' => $this->residentUser->id,
        'is_active' => true,
        'resident_type' => 'owner',
    ]);
});

it('allows residents to create invitations for their unit', function () {
    $this->actingAs($this->residentUser);

    $response = $this->postJson("http://{$this->community->slug}.app.sitios-urbanos.test/api/security/invitations", [
        'unit_id' => $this->unit->id,
        'type' => 'qr',
        'notes' => 'A test invitation',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('type', 'qr');

    $this->assertDatabaseHas('access_invitations', [
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'status' => 'active',
    ]);
});

it('prevents residents from creating invitations for other units', function () {
    $otherUnit = Unit::factory()->create(['community_id' => $this->community->id]);

    $this->actingAs($this->residentUser);

    $response = $this->postJson("http://{$this->community->slug}.app.sitios-urbanos.test/api/security/invitations", [
        'unit_id' => $otherUnit->id,
        'notes' => 'Tying to create for someone else',
    ]);

    $response->assertStatus(403);
});

it('allows admins to create invitations for any unit', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson("http://{$this->community->slug}.app.sitios-urbanos.test/api/security/invitations", [
        'unit_id' => $this->unit->id,
        'notes' => 'Admin created',
    ]);

    $response->assertStatus(201);
});

it('allows guards to consume an invitation safely', function () {
    $invitation = AccessInvitation::create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'created_by' => $this->residentUser->id,
        'code' => 'TESTCODE123',
        'status' => 'active',
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($this->guard);

    $response = $this->patchJson("http://{$this->community->slug}.app.sitios-urbanos.test/api/security/invitations/{$invitation->id}/consume");

    $response->assertStatus(200);
    $this->assertEquals('used', $invitation->fresh()->status);

    $this->assertDatabaseHas('security_logs', [
        'community_id' => $this->community->id,
        'action' => 'access_invitation_consumed',
        'subject_id' => $invitation->id,
    ]);
});

it('prevents consumption of expired invitations', function () {
    $invitation = AccessInvitation::create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'created_by' => $this->residentUser->id,
        'code' => 'TESTCODE123',
        'status' => 'active',
        'expires_at' => now()->subDay(),
    ]);

    $this->actingAs($this->guard);

    $response = $this->patchJson("http://{$this->community->slug}.app.sitios-urbanos.test/api/security/invitations/{$invitation->id}/consume");

    $response->assertStatus(422);
    $this->assertEquals('expired', $invitation->fresh()->status);
});

it('allows revoking an invitation safely', function () {
    $invitation = AccessInvitation::create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'created_by' => $this->residentUser->id,
        'code' => 'TESTCODE123',
        'status' => 'active',
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($this->residentUser);

    $response = $this->patchJson("http://{$this->community->slug}.app.sitios-urbanos.test/api/security/invitations/{$invitation->id}/revoke");
    $response->assertStatus(200);

    $this->assertEquals('revoked', $invitation->fresh()->status);
});

it('enforces tenant isolation', function () {
    $otherCommunity = Community::factory()->create(['slug' => 'other-community']);
    $otherAdmin = User::factory()->create();
    $otherAdmin->communities()->attach($otherCommunity->id, ['role' => 'admin']);

    $invitation = AccessInvitation::create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'created_by' => $this->residentUser->id,
        'code' => 'TESTCODE123',
        'status' => 'active',
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($otherAdmin);

    $response = $this->getJson("http://{$otherCommunity->slug}.app.sitios-urbanos.test/api/security/invitations/{$invitation->id}");

    $response->assertStatus(404);
});
