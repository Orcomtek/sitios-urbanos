<?php

namespace Tests\Feature\Tenant;

use App\Models\Community;
use App\Models\User;
use Database\Factories\CommunityFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CockpitAccessTest extends TestCase
{
    use RefreshDatabase;

    protected array $tenantHeaders;
    protected Community $community;

    protected function setUp(): void
    {
        parent::setUp();

        $this->community = CommunityFactory::new()->create([
            'slug' => 'test-community'
        ]);

        $this->tenantHeaders = [
            'Host' => 'test-community.' . config('app.central_domain', 'sitios-urbanos.test')
        ];
    }

    public function test_admin_can_access_all_cockpit_routes(): void
    {
        $admin = UserFactory::new()->create();
        $this->community->users()->attach($admin, ['role' => 'admin']);

        $response = $this->actingAs($admin)->get('http://' . $this->tenantHeaders['Host'] . '/cockpit/dashboard');
        $response->assertOk();

        $response = $this->actingAs($admin)->get('http://' . $this->tenantHeaders['Host'] . '/cockpit/work-queue');
        $response->assertOk();

        $response = $this->actingAs($admin)->get('http://' . $this->tenantHeaders['Host'] . '/cockpit/admin-work-queue');
        $response->assertOk();
    }

    public function test_guard_can_access_dashboard_and_work_queue_but_not_admin_queue(): void
    {
        $guard = UserFactory::new()->create();
        $this->community->users()->attach($guard, ['role' => 'guard']);

        $response = $this->actingAs($guard)->get('http://' . $this->tenantHeaders['Host'] . '/cockpit/dashboard');
        $response->assertOk();

        $response = $this->actingAs($guard)->get('http://' . $this->tenantHeaders['Host'] . '/cockpit/work-queue');
        $response->assertOk();

        $response = $this->actingAs($guard)->get('http://' . $this->tenantHeaders['Host'] . '/cockpit/admin-work-queue');
        $response->assertForbidden();
    }

    public function test_resident_cannot_access_any_cockpit_route(): void
    {
        $resident = UserFactory::new()->create();
        $this->community->users()->attach($resident, ['role' => 'resident']);

        $response = $this->actingAs($resident)->get('http://' . $this->tenantHeaders['Host'] . '/cockpit/dashboard');
        $response->assertForbidden();

        $response = $this->actingAs($resident)->get('http://' . $this->tenantHeaders['Host'] . '/cockpit/work-queue');
        $response->assertForbidden();

        $response = $this->actingAs($resident)->get('http://' . $this->tenantHeaders['Host'] . '/cockpit/admin-work-queue');
        $response->assertForbidden();
    }
}
