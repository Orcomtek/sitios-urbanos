<?php

namespace Tests\Feature\Cockpit;

use App\Enums\CommunityRole;
use App\Models\AccessInvitation;
use App\Models\Community;
use App\Models\EmergencyEvent;
use App\Models\Package;
use App\Models\Unit;
use App\Models\User;
use App\Models\Visitor;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkQueueTest extends TestCase
{
    use RefreshDatabase;

    private Community $community;
    private $centralDomain;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->centralDomain = config('app.central_domain', 'sitios-urbanos.test');
        
        $this->community = Community::factory()->create(['slug' => 'test-workqueue']);
    }

    private function getTenantUrl(string $path = ''): string
    {
        return "http://{$this->community->slug}.{$this->centralDomain}/api/cockpit/work-queue";
    }

    private function createUserWithRole(CommunityRole $role): User
    {
        $user = User::factory()->create();
        $this->community->users()->attach($user, ['role' => $role->value]);
        return $user;
    }

    public function test_resident_cannot_access_work_queue()
    {
        $residentUser = $this->createUserWithRole(CommunityRole::Resident);

        $response = $this->actingAs($residentUser)
            ->getJson($this->getTenantUrl());

        $response->assertForbidden();
    }

    public function test_admin_and_guard_can_access_work_queue()
    {
        // 1. Guard access
        $guard = $this->createUserWithRole(CommunityRole::Guard);
        
        $response = $this->actingAs($guard)->getJson($this->getTenantUrl());
        $response->assertOk();
        $response->assertJsonPath('data.role', CommunityRole::Guard->value);

        // 2. Admin access
        $admin = $this->createUserWithRole(CommunityRole::Admin);
        
        $response = $this->actingAs($admin)->getJson($this->getTenantUrl());
        $response->assertOk();
        $response->assertJsonPath('data.role', CommunityRole::Admin->value);
    }

    public function test_work_queue_returns_actionable_items_only_and_respects_tenant_isolation()
    {
        $admin = $this->createUserWithRole(CommunityRole::Admin);
        
        app(TenantContext::class)->set($this->community);

        $unit = Unit::factory()->create(['community_id' => $this->community->id]);
        $otherUser = User::factory()->create();

        // Need standard active/pending items
        Visitor::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'pending']);
        // Need completed item: should NOT be returned
        Visitor::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'entered']);
        
        Package::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'received', 'received_by' => $otherUser->id]);
        Package::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'delivered', 'received_by' => $otherUser->id]);

        EmergencyEvent::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'active', 'triggered_by' => $otherUser->id]);
        EmergencyEvent::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'acknowledged', 'triggered_by' => $otherUser->id]);

        AccessInvitation::create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'active', 'expires_at' => now()->addDays(1), 'created_by' => $otherUser->id, 'code' => 'ACTIVE01']);
        AccessInvitation::create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'revoked', 'expires_at' => now()->addDays(1), 'created_by' => $otherUser->id, 'code' => 'REVOKED1']);
        AccessInvitation::create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'active', 'expires_at' => now()->subDays(1), 'created_by' => $otherUser->id, 'code' => 'EXPIRED1']);

        // Data from another community to ensure tenant scoping
        $otherCommunity = Community::factory()->create();
        app(TenantContext::class)->set($otherCommunity);
        $otherUnit = Unit::factory()->create(['community_id' => $otherCommunity->id]);
        Visitor::factory()->create(['community_id' => $otherCommunity->id, 'unit_id' => $otherUnit->id, 'status' => 'pending']);

        // Back to main community
        app(TenantContext::class)->set($this->community);

        $response = $this->actingAs($admin)->getJson($this->getTenantUrl());

        $response->assertOk();
        
        $tasks = $response->json('data.tasks');
        
        // We should have exactly 4 items
        $this->assertCount(4, $tasks);

        $types = collect($tasks)->pluck('type')->toArray();
        $this->assertContains('visitor_pending', $types);
        $this->assertContains('package_received', $types);
        $this->assertContains('emergency_active', $types);
        $this->assertContains('invitation_active', $types);

        $response->assertJsonStructure([
            'data' => [
                'role',
                'generated_at',
                'tasks' => [
                    '*' => [
                        'id',
                        'type',
                        'unit' => ['id', 'unit_number'],
                        'label',
                        'action'
                    ]
                ]
            ]
        ]);
        
        // Ensure actions are exact
        $actions = collect($tasks)->pluck('action')->toArray();
        $this->assertContains('enter', $actions);
        $this->assertContains('deliver', $actions);
        $this->assertContains('acknowledge', $actions);
        $this->assertContains('consume', $actions);
    }
}
