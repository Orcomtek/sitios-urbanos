<?php

namespace Tests\Feature\Dashboard;

use App\Enums\CommunityRole;
use App\Enums\InvoiceStatus;
use App\Models\Community;
use App\Models\EmergencyEvent;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Unit;
use App\Models\User;
use App\Models\Visitor;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private Community $community;

    private $centralDomain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->centralDomain = config('app.central_domain', 'sitios-urbanos.test');

        $this->community = Community::factory()->create(['slug' => 'test-dashboard']);
    }

    private function getTenantUrl(string $path = ''): string
    {
        return "http://{$this->community->slug}.{$this->centralDomain}/api/cockpit/dashboard";
    }

    private function createUserWithRole(CommunityRole $role): User
    {
        $user = User::factory()->create();
        $this->community->users()->attach($user, ['role' => $role->value]);

        return $user;
    }

    public function test_resident_cannot_access_dashboard()
    {
        $residentUser = $this->createUserWithRole(CommunityRole::Resident);

        $response = $this->actingAs($residentUser)
            ->getJson($this->getTenantUrl());

        $response->assertForbidden();
    }

    public function test_guard_sees_operational_widgets_only()
    {
        $guard = $this->createUserWithRole(CommunityRole::Guard);

        // Required to avoid TenantScope issues when creating factories directly
        app(TenantContext::class)->set($this->community);

        $unit = Unit::factory()->create(['community_id' => $this->community->id]);

        EmergencyEvent::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'active']);
        Visitor::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'pending']);
        Package::factory()->create(['community_id' => $this->community->id, 'unit_id' => $unit->id, 'status' => 'received']);

        // Data from another community to ensure tenant scoping
        $otherCommunity = Community::factory()->create();
        $otherUnit = Unit::factory()->create(['community_id' => $otherCommunity->id]);
        EmergencyEvent::factory()->create(['community_id' => $otherCommunity->id, 'unit_id' => $otherUnit->id, 'status' => 'active']);

        $response = $this->actingAs($guard)->getJson($this->getTenantUrl());

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'role',
                'generated_at',
                'widgets' => [
                    'emergencies' => ['active_count', 'recent_active'],
                    'visitors' => ['pending_count', 'entered_count'],
                    'packages' => ['pending_pickup_count', 'recent_pending'],
                ],
            ],
        ]);

        $response->assertJsonMissingPath('data.widgets.pqrs');
        $response->assertJsonMissingPath('data.widgets.polls');
        $response->assertJsonMissingPath('data.widgets.finance');

        $this->assertEquals(1, $response->json('data.widgets.emergencies.active_count'));
        $this->assertEquals(1, $response->json('data.widgets.visitors.pending_count'));
        $this->assertEquals(1, $response->json('data.widgets.packages.pending_pickup_count'));
    }

    public function test_admin_sees_all_widgets_including_finance()
    {
        $admin = $this->createUserWithRole(CommunityRole::Admin);

        app(TenantContext::class)->set($this->community);

        $unit = Unit::factory()->create(['community_id' => $this->community->id]);
        Invoice::factory()->create([
            'community_id' => $this->community->id,
            'unit_id' => $unit->id,
            'status' => InvoiceStatus::PENDING,
            'amount' => 50000,
            'issued_at' => now(),
            'due_date' => now()->addDays(5),
            'type' => 'admin_fee',
        ]);

        $response = $this->actingAs($admin)->getJson($this->getTenantUrl());

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'role',
                'generated_at',
                'widgets' => [
                    'emergencies',
                    'visitors',
                    'packages',
                    'pqrs',
                    'polls',
                    'finance' => [
                        'pending_invoices_count',
                        'pending_amount',
                        'recent_confirmed_payments_count',
                    ],
                ],
            ],
        ]);

        $this->assertEquals(1, $response->json('data.widgets.finance.pending_invoices_count'));
        $this->assertEquals(50000, $response->json('data.widgets.finance.pending_amount'));
    }
}
