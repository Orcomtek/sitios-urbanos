<?php

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\Package;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->community = Community::factory()->create();
    $this->centralDomain = config('app.central_domain', 'sitios-urbanos.test');
    $this->baseUrl = "http://{$this->community->slug}.{$this->centralDomain}";
    $this->admin = User::factory()->create();
    $this->admin->communities()->attach($this->community, ['role' => CommunityRole::Admin]);

    $this->unit1 = Unit::factory()->create(['community_id' => $this->community->id]);
    $this->unit2 = Unit::factory()->create(['community_id' => $this->community->id]);

    $this->residentUser = User::factory()->create();
    $this->residentUser->communities()->attach($this->community, ['role' => CommunityRole::Resident]);

    Resident::factory()->create([
        'unit_id' => $this->unit1->id,
        'user_id' => $this->residentUser->id,
        'community_id' => $this->community->id,
    ]);
});

it('allows admin to register a received package', function () {
    $response = $this->actingAs($this->admin)
        ->postJson("{$this->baseUrl}/api/security/packages", [
            'unit_id' => $this->unit1->id,
            'carrier' => 'FedEx',
            'tracking_number' => '12345ABC',
            'sender_name' => 'Amazon',
            'recipient_name' => 'John Doe',
            'description' => 'A small box',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'received')
        ->assertJsonPath('data.carrier', 'FedEx');

    $this->assertDatabaseHas('packages', [
        'unit_id' => $this->unit1->id,
        'received_by' => $this->admin->id,
        'status' => 'received',
    ]);
});

it('prevents resident from registering a package', function () {
    $response = $this->actingAs($this->residentUser)
        ->postJson("{$this->baseUrl}/api/security/packages", [
            'unit_id' => $this->unit1->id,
            'carrier' => 'FedEx',
        ]);

    $response->assertStatus(403);
});

it('allows resident to see only packages for their unit', function () {
    Package::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit1->id,
        'received_by' => $this->admin->id,
        'status' => 'received',
        'received_at' => now(),
    ]);

    Package::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit2->id,
        'received_by' => $this->admin->id,
        'status' => 'received',
        'received_at' => now(),
    ]);

    $response = $this->actingAs($this->residentUser)
        ->getJson("{$this->baseUrl}/api/security/packages");

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.unit_id'))->toBe($this->unit1->id);
});

it('allows admin to see all packages in the community', function () {
    Package::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit1->id,
        'received_by' => $this->admin->id,
        'status' => 'received',
        'received_at' => now(),
    ]);

    Package::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit2->id,
        'received_by' => $this->admin->id,
        'status' => 'received',
        'received_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson("{$this->baseUrl}/api/security/packages");

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(2);
});

it('allows admin to deliver a package', function () {
    $package = Package::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit1->id,
        'received_by' => $this->admin->id,
        'status' => 'received',
        'received_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->patchJson("{$this->baseUrl}/api/security/packages/{$package->id}/deliver", [
            'notes' => 'Handed over to wife',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'delivered')
        ->assertJsonPath('data.notes', 'Handed over to wife');

    $this->assertDatabaseHas('packages', [
        'id' => $package->id,
        'status' => 'delivered',
        'delivered_by' => $this->admin->id,
    ]);
});

it('allows admin to return a package', function () {
    $package = Package::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit1->id,
        'received_by' => $this->admin->id,
        'status' => 'received',
        'received_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->patchJson("{$this->baseUrl}/api/security/packages/{$package->id}/return", [
            'notes' => 'Recipient refused',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'returned')
        ->assertJsonPath('data.notes', 'Recipient refused');

    // Using delivered_by to record who processed the return
    $this->assertDatabaseHas('packages', [
        'id' => $package->id,
        'status' => 'returned',
        'delivered_by' => $this->admin->id,
    ]);
});

it('prevents acting on an already processed package', function () {
    $package = Package::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit1->id,
        'received_by' => $this->admin->id,
        'status' => 'delivered',
        'received_at' => now(),
        'delivered_at' => now(),
        'delivered_by' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->patchJson("{$this->baseUrl}/api/security/packages/{$package->id}/return");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('isolates packages between tenants', function () {
    $otherCommunity = Community::factory()->create();
    $otherAdmin = User::factory()->create();
    $otherAdmin->communities()->attach($otherCommunity, ['role' => CommunityRole::Admin]);
    $otherUnit = Unit::factory()->create(['community_id' => $otherCommunity->id]);

    $package = Package::factory()->create([
        'community_id' => $otherCommunity->id,
        'unit_id' => $otherUnit->id,
        'received_by' => $otherAdmin->id,
        'status' => 'received',
        'received_at' => now(),
    ]);

    // Admin from first community trys to see package from second community
    $response = $this->actingAs($this->admin)
        ->getJson("{$this->baseUrl}/api/security/packages/{$package->id}");

    $response->assertStatus(404);
});
