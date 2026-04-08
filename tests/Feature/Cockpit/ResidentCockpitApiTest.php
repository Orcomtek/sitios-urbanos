<?php

namespace Tests\Feature\Cockpit;

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentCockpitApiTest extends TestCase
{
    use RefreshDatabase;

    protected Community $community;

    protected string $domain;

    protected User $residentUser;

    protected User $adminUser;

    protected User $guardUser;

    protected Unit $unit1;

    protected Unit $unit2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->community = Community::factory()->create(['slug' => 'test-resident']);
        $this->domain = 'test-resident.'.config('app.central_domain');

        $this->adminUser = User::factory()->create();
        $this->adminUser->communities()->attach($this->community->id, ['role' => CommunityRole::Admin->value]);

        $this->guardUser = User::factory()->create();
        $this->guardUser->communities()->attach($this->community->id, ['role' => CommunityRole::Guard->value]);

        $this->residentUser = User::factory()->create();
        $this->residentUser->communities()->attach($this->community->id, ['role' => CommunityRole::Resident->value]);

        $this->unit1 = Unit::factory()->create(['community_id' => $this->community->id]);
        $this->unit2 = Unit::factory()->create(['community_id' => $this->community->id]);

        Resident::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $this->unit1->id,
            'user_id' => $this->residentUser->id,
            'is_active' => true,
        ]);

        Resident::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $this->unit2->id,
            'user_id' => $this->residentUser->id,
            'is_active' => true,
        ]);
    }

    protected function makeRequest(User $user, string $method, string $path, array $data = [])
    {
        $url = "http://{$this->domain}{$path}";

        return $this->actingAs($user)->json($method, $url, $data);
    }

    public function test_resident_can_access_cockpit()
    {
        $response = $this->makeRequest($this->residentUser, 'GET', '/api/cockpit/resident');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'role',
                'finance' => ['pending_count', 'pending_amount', 'recent_invoices'],
                'packages',
                'invitations',
                'pqrs',
                'visitors',
            ],
        ]);
    }

    public function test_admin_is_forbidden_from_resident_cockpit()
    {
        $response = $this->makeRequest($this->adminUser, 'GET', '/api/cockpit/resident');
        $response->assertStatus(403);
    }

    public function test_guard_is_forbidden_from_resident_cockpit()
    {
        $response = $this->makeRequest($this->guardUser, 'GET', '/api/cockpit/resident');
        $response->assertStatus(403);
    }

    public function test_cockpit_includes_only_active_unit_data()
    {
        // 1. Give unit 1 a pending invoice
        $invoice = Invoice::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $this->unit1->id,
            'status' => 'pending',
            'amount' => 50000,
            'type' => 'admin_fee',
            'description' => 'Test',
            'issued_at' => now(),
            'due_date' => now()->addDays(5),
        ]);

        // 2. Give unit 2 a package
        $package = Package::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $this->unit2->id,
            'status' => 'received',
        ]);

        // 3. Create a unit 3 belonging to SOMEONE ELSE in the same community (or no resident)
        $unit3 = Unit::factory()->create(['community_id' => $this->community->id]);
        $otherInvoice = Invoice::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $unit3->id,
            'status' => 'pending',
            'amount' => 100000,
            'type' => 'admin_fee',
            'description' => 'Test Other',
            'issued_at' => now(),
            'due_date' => now()->addDays(5),
        ]);

        $response = $this->makeRequest($this->residentUser, 'GET', '/api/cockpit/resident');
        $response->assertStatus(200);

        // Assert it sees invoice for unit 1, package for unit 2, but NOT invoice for unit 3
        $this->assertEquals(50000, $response->json('data.finance.pending_amount'));
        $this->assertCount(1, $response->json('data.packages'));
        $this->assertEquals($package->id, $response->json('data.packages.0.id'));
    }

    public function test_tenant_isolation()
    {
        $community2 = Community::factory()->create(['slug' => 'test2']);
        $domain2 = 'test2.'.config('app.central_domain');
        $user2 = User::factory()->create();
        $user2->communities()->attach($community2->id, ['role' => CommunityRole::Resident->value]);

        // Even if user2 tries to access community 1, should be 403 or 404 because not part of it
        $url = "http://{$this->domain}/api/cockpit/resident";
        $res = $this->actingAs($user2)->json('GET', $url);

        // Actually TenantMiddleware returns 404 if user is not attached to this tenant subdomain.
        $res->assertStatus(404);
    }
}
