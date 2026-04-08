<?php

namespace Tests\Feature\Cockpit;

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentCockpitWebTest extends TestCase
{
    use RefreshDatabase;

    protected Community $community;

    protected string $domain;

    protected User $residentUser;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->community = Community::factory()->create(['slug' => 'test-resident']);
        $this->domain = 'test-resident.'.config('app.central_domain');

        $this->adminUser = User::factory()->create();
        $this->adminUser->communities()->attach($this->community->id, ['role' => CommunityRole::Admin->value]);

        $this->residentUser = User::factory()->create();
        $this->residentUser->communities()->attach($this->community->id, ['role' => CommunityRole::Resident->value]);
    }

    protected function makeRequest(User $user, string $method, string $path, array $data = [])
    {
        $url = "http://{$this->domain}{$path}";

        return $this->actingAs($user)->get($url);
    }

    public function test_resident_can_view_cockpit_page()
    {
        $response = $this->makeRequest($this->residentUser, 'GET', '/cockpit/resident');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Cockpit/ResidentCockpit'));
    }

    public function test_admin_forbidden_from_resident_page()
    {
        $response = $this->makeRequest($this->adminUser, 'GET', '/cockpit/resident');
        $response->assertStatus(403);
    }
}
