<?php

namespace Tests\Feature\Cockpit;

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\SecurityLog;
use App\Models\User;
use App\Notifications\Security\VisitorArrivalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTimelineTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $resident;
    private Community $community;
    private Community $otherCommunity;
    private string $centralDomain;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->centralDomain = config('app.central_domain', 'sitios-urbanos.test');

        $this->community = Community::factory()->create(['slug' => 'test-community']);
        $this->otherCommunity = Community::factory()->create(['slug' => 'other-community']);

        $this->admin = User::factory()->create();
        $this->community->users()->attach($this->admin, ['role' => CommunityRole::Admin->value]);

        $this->resident = User::factory()->create();
        $this->community->users()->attach($this->resident, ['role' => CommunityRole::Resident->value]);
    }

    public function test_admin_can_view_activity_timeline_with_tenant_isolation()
    {
        // Log in current community
        SecurityLog::create([
            'community_id' => $this->community->id,
            'actor_id' => $this->admin->id,
            'action' => 'visitor_entered',
            'details' => ['message' => 'Visitor entered'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
        ]);

        // Log in other community
        SecurityLog::create([
            'community_id' => $this->otherCommunity->id,
            'actor_id' => $this->admin->id,
            'action' => 'package_delivered',
            'details' => ['message' => 'Package delivered'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('http://' . $this->community->slug . '.' . $this->centralDomain . '/api/cockpit/activity');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'visitor_entered');
    }

    public function test_resident_can_only_view_own_filtered_notifications()
    {
        // Admin action log (resident should NOT see this based on rules)
        SecurityLog::create([
            'community_id' => $this->community->id,
            'actor_id' => $this->admin->id,
            'action' => 'visitor_registered',
            'details' => [],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
        ]);

        // Give the resident a notification for this community
        // Use a generic database notification via notification fake/or just insert into db if Notifiable trait is configured to use db
        // But the easiest way is to push a notification if we have a real one, or just use the DB table directly.
        $this->resident->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\Security\PackageReceivedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->resident->id,
            'data' => [
                'community_id' => $this->community->id,
                'title' => 'Paquete recibido',
                'message' => 'Tienes un nuevo paquete',
            ],
            'read_at' => null,
        ]);

        // Give resident a notification for another community
        $this->resident->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\Security\PackageReceivedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->resident->id,
            'data' => [
                'community_id' => $this->otherCommunity->id,
                'title' => 'Paquete recibido',
                'message' => 'Otro paquete',
            ],
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->resident)
            ->getJson('http://' . $this->community->slug . '.' . $this->centralDomain . '/api/cockpit/activity');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.source', 'notification')
            ->assertJsonPath('data.0.type', 'package_received')
            ->assertJsonPath('data.0.title', 'Paquete recibido');
    }
}
