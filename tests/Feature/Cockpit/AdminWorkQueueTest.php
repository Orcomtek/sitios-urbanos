<?php

namespace Tests\Feature\Cockpit;

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\Governance\Announcement;
use App\Models\Governance\Poll;
use App\Models\Invoice;
use App\Models\Pqrs;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkQueueTest extends TestCase
{
    use RefreshDatabase;

    private Community $community;

    private $centralDomain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->centralDomain = config('app.central_domain', 'sitios-urbanos.test');

        $this->community = Community::factory()->create(['slug' => 'test-adminqueue']);
    }

    private function getTenantUrl(string $path = ''): string
    {
        return "http://{$this->community->slug}.{$this->centralDomain}/api/cockpit/admin-work-queue";
    }

    private function createUserWithRole(CommunityRole $role): User
    {
        $user = User::factory()->create();
        $this->community->users()->attach($user, ['role' => $role->value]);

        return $user;
    }

    public function test_resident_cannot_access_admin_work_queue()
    {
        $residentUser = $this->createUserWithRole(CommunityRole::Resident);

        $response = $this->actingAs($residentUser)
            ->getJson($this->getTenantUrl());

        $response->assertForbidden();
    }

    public function test_guard_cannot_access_admin_work_queue()
    {
        $guard = $this->createUserWithRole(CommunityRole::Guard);

        $response = $this->actingAs($guard)
            ->getJson($this->getTenantUrl());

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_work_queue()
    {
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
        $resident = Resident::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'user_id' => $otherUser->id]);

        // 1. PQRS
        Pqrs::factory()->create(['community_id' => $this->community->id, 'resident_id' => $resident->id, 'status' => 'open']);
        Pqrs::factory()->create(['community_id' => $this->community->id, 'resident_id' => $resident->id, 'status' => 'closed']);

        // 2. Polls
        Poll::create(['community_id' => $this->community->id, 'created_by' => $admin->id, 'title' => 'Test Poll 1', 'description' => 'Desc', 'type' => 'general', 'status' => 'open']);
        Poll::create(['community_id' => $this->community->id, 'created_by' => $admin->id, 'title' => 'Test Poll 2', 'description' => 'Desc', 'type' => 'general', 'status' => 'closed']);

        // 3. Invoices
        Invoice::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $unit->id,
            'status' => 'pending',
            'amount' => 1000,
            'type' => 'admin_fee',
            'description' => 'Test',
            'issued_at' => now(),
            'due_date' => now()->addDays(5),
        ]);
        Invoice::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $unit->id,
            'status' => 'paid',
            'amount' => 1000,
            'type' => 'admin_fee',
            'description' => 'Test',
            'issued_at' => now(),
            'due_date' => now()->addDays(5),
        ]);

        // 4. Announcements
        Announcement::create(['community_id' => $this->community->id, 'title' => 'A1', 'content' => 'x', 'type' => 'general', 'created_by' => $admin->id, 'starts_at' => now()->subDay(), 'ends_at' => null]);
        Announcement::create(['community_id' => $this->community->id, 'title' => 'A2', 'content' => 'x', 'type' => 'general', 'created_by' => $admin->id, 'starts_at' => now()->addDay(), 'ends_at' => null]);
        Announcement::create(['community_id' => $this->community->id, 'title' => 'A3', 'content' => 'x', 'type' => 'general', 'created_by' => $admin->id, 'starts_at' => now()->subDays(2), 'ends_at' => now()->subDay()]);

        // Data from another community to ensure tenant scoping
        $otherCommunity = Community::factory()->create();
        app(TenantContext::class)->set($otherCommunity);
        $otherUnit = Unit::factory()->create(['community_id' => $otherCommunity->id]);
        $otherResident = Resident::factory()->create(['community_id' => $otherCommunity->id, 'unit_id' => $otherUnit->id, 'user_id' => $otherUser->id]);
        Pqrs::factory()->create(['community_id' => $otherCommunity->id, 'resident_id' => $otherResident->id, 'status' => 'open']);

        // Back to main community
        app(TenantContext::class)->set($this->community);

        $response = $this->actingAs($admin)->getJson($this->getTenantUrl());

        $response->assertOk();

        $tasks = $response->json('data.tasks');

        // We should have exactly 4 items
        $this->assertCount(4, $tasks);

        $types = collect($tasks)->pluck('type')->toArray();
        $this->assertContains('pqrs_open', $types);
        $this->assertContains('poll_active', $types);
        $this->assertContains('invoice_pending', $types);
        $this->assertContains('announcement_active', $types);

        $response->assertJsonStructure([
            'data' => [
                'role',
                'generated_at',
                'tasks' => [
                    '*' => [
                        'id',
                        'type',
                        'unit',
                        'label',
                        'action',
                    ],
                ],
            ],
        ]);

        // Ensure actions are exact
        $actions = collect($tasks)->pluck('action')->toArray();
        $this->assertContains('respond', $actions);
        $this->assertContains('review', $actions);
        $this->assertContains('review', $actions);
        $this->assertContains('view', $actions);
    }
}
