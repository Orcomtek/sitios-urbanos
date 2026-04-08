<?php

namespace Tests\Feature\Tenant;

use App\Models\Community;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResidentOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected Community $community;
    protected User $residentUser;
    protected User $adminUser;
    protected User $guardUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->community = Community::factory()->create();

        $this->residentUser = User::factory()->create();
        $this->community->users()->attach($this->residentUser, ['role' => 'resident']);

        $this->adminUser = User::factory()->create();
        $this->community->users()->attach($this->adminUser, ['role' => 'admin']);

        $this->guardUser = User::factory()->create();
        $this->community->users()->attach($this->guardUser, ['role' => 'guard']);

        $unit = Unit::factory()->create(['community_id' => $this->community->id]);
        Resident::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $unit->id,
            'user_id' => $this->residentUser->id,
            'resident_type' => 'tenant',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function resident_can_access_resident_operations_page()
    {
        $response = $this->actingAs($this->residentUser)
            ->withSession(['tenant.community_id' => $this->community->id])
            ->get("http://{$this->community->slug}." . config('app.central_domain') . "/cockpit/resident/operations");

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_cannot_access_resident_operations_page()
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['tenant.community_id' => $this->community->id])
            ->get("http://{$this->community->slug}." . config('app.central_domain') . "/cockpit/resident/operations");

        $response->assertStatus(403);
    }

    #[Test]
    public function guard_cannot_access_resident_operations_page()
    {
        $response = $this->actingAs($this->guardUser)
            ->withSession(['tenant.community_id' => $this->community->id])
            ->get("http://{$this->community->slug}." . config('app.central_domain') . "/cockpit/resident/operations");

        $response->assertStatus(403);
    }
}
