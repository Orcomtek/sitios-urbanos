<?php

namespace Tests\Feature\Security;

use App\Enums\CommunityRole;
use App\Events\Security\EmergencyEventCreated;
use App\Models\Community;
use App\Models\EmergencyEvent;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EmergencyEventTest extends TestCase
{
    use RefreshDatabase;

    private Community $community;

    private Unit $unit;

    private User $resident;

    private User $admin;

    private User $guard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->community = Community::factory()->create();
        $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);

        $this->resident = User::factory()->create();
        $this->community->users()->attach($this->resident, ['role' => CommunityRole::Resident->value]);
        Resident::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $this->unit->id,
            'user_id' => $this->resident->id,
        ]);

        $this->admin = User::factory()->create();
        $this->community->users()->attach($this->admin, ['role' => CommunityRole::Admin->value]);

        $this->guard = User::factory()->create();
        $this->community->users()->attach($this->guard, ['role' => CommunityRole::Guard->value]);
    }

    private function getUrl(string $path): string
    {
        return 'http://'.$this->community->slug.'.'.config('app.central_domain').$path;
    }

    public function test_resident_can_trigger_emergency()
    {
        Event::fake([EmergencyEventCreated::class]);

        $response = $this->actingAs($this->resident)->postJson($this->getUrl('/api/security/emergencies'), [
            'unit_id' => $this->unit->id,
            'type' => 'panic',
            'description' => 'Help!',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('emergency_events', [
            'unit_id' => $this->unit->id,
            'type' => 'panic',
            'status' => 'active',
            'triggered_by' => $this->resident->id,
        ]);

        $this->assertDatabaseHas('security_logs', [
            'action' => 'emergency_triggered',
        ]);

        Event::assertDispatched(EmergencyEventCreated::class);
    }

    public function test_resident_cannot_trigger_duplicate_active_emergency()
    {
        $this->actingAs($this->resident)->postJson($this->getUrl('/api/security/emergencies'), [
            'unit_id' => $this->unit->id,
            'type' => 'panic',
        ]);

        $response = $this->actingAs($this->resident)->postJson($this->getUrl('/api/security/emergencies'), [
            'unit_id' => $this->unit->id,
            'type' => 'panic',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('type');
    }

    public function test_resident_cannot_trigger_emergency_for_another_unit()
    {
        $otherUnit = Unit::factory()->create(['community_id' => $this->community->id]);

        $response = $this->actingAs($this->resident)->postJson($this->getUrl('/api/security/emergencies'), [
            'unit_id' => $otherUnit->id,
            'type' => 'panic',
        ]);

        $response->assertStatus(403);
    }

    public function test_guard_can_acknowledge_emergency()
    {
        $emergency = EmergencyEvent::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $this->unit->id,
            'triggered_by' => $this->resident->id,
            'status' => 'active',
            'type' => 'panic',
        ]);

        $response = $this->actingAs($this->guard)->patchJson($this->getUrl("/api/security/emergencies/{$emergency->id}/ack"), [
            'notes' => 'Acknowledged by guard',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('emergency_events', [
            'id' => $emergency->id,
            'status' => 'acknowledged',
            'notes' => 'Acknowledged by guard',
        ]);

        $this->assertDatabaseHas('security_logs', [
            'action' => 'emergency_acknowledged',
        ]);
    }

    public function test_admin_can_resolve_emergency()
    {
        $emergency = EmergencyEvent::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $this->unit->id,
            'triggered_by' => $this->resident->id,
            'status' => 'acknowledged',
            'type' => 'panic',
        ]);

        $response = $this->actingAs($this->admin)->patchJson($this->getUrl("/api/security/emergencies/{$emergency->id}/resolve"), [
            'notes' => 'Issue resolved',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('emergency_events', [
            'id' => $emergency->id,
            'status' => 'resolved',
            'notes' => 'Issue resolved',
        ]);

        $this->assertDatabaseHas('security_logs', [
            'action' => 'emergency_resolved',
        ]);
    }

    public function test_resident_cannot_modify_emergency()
    {
        $emergency = EmergencyEvent::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $this->unit->id,
            'triggered_by' => $this->resident->id,
            'status' => 'active',
            'type' => 'panic',
        ]);

        $response = $this->actingAs($this->resident)->patchJson($this->getUrl("/api/security/emergencies/{$emergency->id}/ack"));

        $response->assertStatus(403);
    }

    public function test_tenant_isolation_is_enforced()
    {
        $otherCommunity = Community::factory()->create();
        $otherUser = User::factory()->create();
        $otherCommunity->users()->attach($otherUser, ['role' => CommunityRole::Guard->value]);

        $emergency = EmergencyEvent::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $this->unit->id,
            'triggered_by' => $this->resident->id,
            'status' => 'active',
            'type' => 'panic',
        ]);

        // Guard from another community tries to access
        $response = $this->actingAs($otherUser)->patchJson(
            'http://'.$otherCommunity->slug.'.'.config('app.central_domain')."/api/security/emergencies/{$emergency->id}/ack"
        );

        $response->assertStatus(404);
    }
}
