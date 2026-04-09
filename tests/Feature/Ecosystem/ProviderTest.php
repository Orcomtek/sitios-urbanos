<?php

use App\Enums\CommunityRole;
use App\Enums\ProviderCategory;
use App\Enums\ProviderStatus;
use App\Models\Community;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->community = Community::factory()->create(['slug' => 'test-community']);
    $this->otherCommunity = Community::factory()->create(['slug' => 'other-community']);

    // Admin
    $this->admin = User::factory()->create();
    $this->admin->communities()->attach($this->community->id, ['role' => CommunityRole::Admin->value]);

    // Resident
    $this->resident = User::factory()->create();
    $this->resident->communities()->attach($this->community->id, ['role' => CommunityRole::Resident->value]);

    // Guards
    $this->guard = User::factory()->create();
    $this->guard->communities()->attach($this->community->id, ['role' => CommunityRole::Guard->value]);

    // Other user
    $this->otherAdmin = User::factory()->create();
    $this->otherAdmin->communities()->attach($this->otherCommunity->id, ['role' => CommunityRole::Admin->value]);

    $this->adminUrl = 'http://test-community.app.sitios-urbanos.test/api/ecosystem/providers';
    $this->otherAdminUrl = 'http://other-community.app.sitios-urbanos.test/api/ecosystem/providers';
});

it('allows admin to create a provider', function () {
    $data = [
        'name' => 'John the Plumber',
        'description' => 'Great plumbing services',
        'category' => ProviderCategory::PLUMBING->value,
        'contact_details' => [
            ['type' => 'phone', 'value' => '+1234567890'],
        ],
        'status' => ProviderStatus::ACTIVE->value,
        'is_recommended' => true,
    ];

    $response = $this->actingAs($this->admin)->postJson($this->adminUrl, $data);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'John the Plumber');
    $response->assertJsonPath('data.category', 'plumbing');

    $this->assertDatabaseHas('providers', [
        'community_id' => $this->community->id,
        'name' => 'John the Plumber',
    ]);
});

it('rejects creation by resident', function () {
    $data = [
        'name' => 'Resident Fake Provider',
        'category' => ProviderCategory::OTHERS->value,
        'contact_details' => [['type' => 'email', 'value' => 'resident@test.com']],
        'status' => ProviderStatus::ACTIVE->value,
    ];

    $response = $this->actingAs($this->resident)->postJson($this->adminUrl, $data);

    $response->assertForbidden();
});

it('restricts listing up to role capabilities (resident only sees active)', function () {
    // Create one active and one inactive provider in same community
    Provider::factory()->create([
        'community_id' => $this->community->id,
        'name' => 'Active Provider',
        'status' => ProviderStatus::ACTIVE->value,
    ]);

    Provider::factory()->create([
        'community_id' => $this->community->id,
        'name' => 'Inactive Provider',
        'status' => ProviderStatus::INACTIVE->value,
    ]);

    // Resident request
    $residentResponse = $this->actingAs($this->resident)->getJson($this->adminUrl);
    $residentResponse->assertOk();
    $residentResponse->assertJsonCount(1, 'data');
    $residentResponse->assertJsonPath('data.0.name', 'Active Provider');

    // Admin request
    $adminResponse = $this->actingAs($this->admin)->getJson($this->adminUrl);
    $adminResponse->assertOk();
    $adminResponse->assertJsonCount(2, 'data');
});

it('allows admin to update a provider', function () {
    $provider = Provider::factory()->create([
        'community_id' => $this->community->id,
        'name' => 'Old Name',
    ]);

    $data = [
        'name' => 'New Name',
        'status' => ProviderStatus::INACTIVE->value,
    ];

    $response = $this->actingAs($this->admin)->patchJson("{$this->adminUrl}/{$provider->id}", $data);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'New Name');
    $response->assertJsonPath('data.status', 'inactive');
});

it('maintains strict tenant isolation for providers', function () {
    $provider = Provider::factory()->create([
        'community_id' => $this->community->id,
    ]);

    // Other admin tries to view/update/delete the provider using their own subdomain
    $responseGet = $this->actingAs($this->otherAdmin)->getJson("{$this->otherAdminUrl}/{$provider->id}");
    $responseGet->assertNotFound();

    $responsePatch = $this->actingAs($this->otherAdmin)->patchJson("{$this->otherAdminUrl}/{$provider->id}", ['name' => 'Hack']);
    $responsePatch->assertNotFound();

    $responseDelete = $this->actingAs($this->otherAdmin)->deleteJson("{$this->otherAdminUrl}/{$provider->id}");
    $responseDelete->assertNotFound();
});

it('soft deletes a provider via DeleteProviderAction when requested by admin', function () {
    $provider = Provider::factory()->create([
        'community_id' => $this->community->id,
    ]);

    $response = $this->actingAs($this->admin)->deleteJson("{$this->adminUrl}/{$provider->id}");

    $response->assertNoContent();

    $this->assertSoftDeleted('providers', [
        'id' => $provider->id,
    ]);
});

it('validates jsonb contact_details array structure strictly', function () {
    $dataWithInvalidDetails = [
        'name' => 'Bad Format',
        'category' => ProviderCategory::PLUMBING->value,
        'status' => ProviderStatus::ACTIVE->value,
        'contact_details' => [
            ['wrong_key' => '123', 'foo' => 'bar'],
        ],
    ];

    $response = $this->actingAs($this->admin)->postJson($this->adminUrl, $dataWithInvalidDetails);

    $response->assertJsonValidationErrors(['contact_details.0.type', 'contact_details.0.value']);
});
