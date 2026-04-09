<?php

use App\Enums\CommunityRole;
use App\Enums\ProviderStatus;
use App\Enums\ServiceRequestStatus;
use App\Enums\ServiceUrgency;
use App\Models\Community;
use App\Models\Provider;
use App\Models\ProviderServiceRequest;
use App\Models\Resident;
use App\Models\User;

beforeEach(function () {
    $this->community = Community::factory()->create();
    $centralDomain = config('app.central_domain', 'sitios-urbanos.test');
    $this->domain = "{$this->community->slug}.{$centralDomain}";
});

it('allows residents to create a service request for an active provider', function () {
    $user = User::factory()->create();
    $user->communities()->attach($this->community, ['role' => CommunityRole::Resident->value]);
    $resident = Resident::factory()->create(['user_id' => $user->id, 'community_id' => $this->community->id]);

    $provider = Provider::factory()->create([
        'community_id' => $this->community->id,
        'status' => ProviderStatus::ACTIVE,
    ]);

    $response = $this->actingAs($user)
        ->postJson("http://{$this->domain}/api/ecosystem/service-requests", [
            'provider_id' => $provider->id,
            'description' => 'I need plumbing help.',
            'urgency' => ServiceUrgency::HIGH->value,
            'scheduled_date' => now()->addDays(2)->format('Y-m-d'),
        ]);

    $response->assertCreated();
    $response->assertJsonPath('data.description', 'I need plumbing help.');

    $this->assertDatabaseHas('provider_service_requests', [
        'resident_id' => $resident->id,
        'provider_id' => $provider->id,
        'urgency' => ServiceUrgency::HIGH->value,
        'status' => ServiceRequestStatus::PENDING->value,
    ]);
});

it('prevents residents from creating requests for inactive providers', function () {
    $user = User::factory()->create();
    $user->communities()->attach($this->community, ['role' => CommunityRole::Resident->value]);
    Resident::factory()->create(['user_id' => $user->id, 'community_id' => $this->community->id]);

    $provider = Provider::factory()->create([
        'community_id' => $this->community->id,
        'status' => ProviderStatus::INACTIVE,
    ]);

    $response = $this->actingAs($user)
        ->postJson("http://{$this->domain}/api/ecosystem/service-requests", [
            'provider_id' => $provider->id,
            'description' => 'Plumbing help.',
            'urgency' => ServiceUrgency::LOW->value,
        ]);

    $response->assertJsonValidationErrors(['provider_id']);
});

it('isolates service requests by tenant', function () {
    $otherCommunity = Community::factory()->create();

    $user = User::factory()->create();
    $user->communities()->attach($this->community, ['role' => CommunityRole::Resident->value]);
    Resident::factory()->create(['user_id' => $user->id, 'community_id' => $this->community->id]);

    $providerOther = Provider::factory()->create([
        'community_id' => $otherCommunity->id,
        'status' => ProviderStatus::ACTIVE,
    ]);

    // Resident tries to use a provider from another community
    $response = $this->actingAs($user)
        ->postJson("http://{$this->domain}/api/ecosystem/service-requests", [
            'provider_id' => $providerOther->id,
            'description' => 'Cross tenant attack',
            'urgency' => ServiceUrgency::LOW->value,
        ]);

    $response->assertJsonValidationErrors(['provider_id']);
});

it('allows residents to see ONLY their own requests', function () {
    $userOne = User::factory()->create();
    $userOne->communities()->attach($this->community, ['role' => CommunityRole::Resident->value]);
    Resident::factory()->create(['user_id' => $userOne->id, 'community_id' => $this->community->id]);

    $userTwo = User::factory()->create();
    $userTwo->communities()->attach($this->community, ['role' => CommunityRole::Resident->value]);
    $residentTwo = Resident::factory()->create(['user_id' => $userTwo->id, 'community_id' => $this->community->id]);

    // Create request for user two
    $requestTwo = ProviderServiceRequest::factory()->create([
        'community_id' => $this->community->id,
        'resident_id' => $residentTwo->id,
    ]);

    // User ONE tries to view
    $response = $this->actingAs($userOne)->getJson("http://{$this->domain}/api/ecosystem/service-requests");

    $response->assertOk();
    $response->assertJsonCount(0, 'data'); // Should not see User Two's request

    $showResponse = $this->actingAs($userOne)->getJson("http://{$this->domain}/api/ecosystem/service-requests/{$requestTwo->id}");
    $showResponse->assertForbidden();
});

it('allows administrators to view any request in the community', function () {
    $admin = User::factory()->create();
    $admin->communities()->attach($this->community, ['role' => CommunityRole::Admin->value]);

    $request = ProviderServiceRequest::factory()->create([
        'community_id' => $this->community->id,
    ]);

    $response = $this->actingAs($admin)->getJson("http://{$this->domain}/api/ecosystem/service-requests");
    $response->assertOk()->assertJsonCount(1, 'data');

    $showResponse = $this->actingAs($admin)->getJson("http://{$this->domain}/api/ecosystem/service-requests/{$request->id}");
    $showResponse->assertOk();
});

it('denies guards any access to service requests', function () {
    $guard = User::factory()->create();
    $guard->communities()->attach($this->community, ['role' => CommunityRole::Guard->value]);

    $response = $this->actingAs($guard)->getJson("http://{$this->domain}/api/ecosystem/service-requests");
    $response->assertForbidden();
});

it('allows residents to cancel pending service requests', function () {
    $user = User::factory()->create();
    $user->communities()->attach($this->community, ['role' => CommunityRole::Resident->value]);
    $resident = Resident::factory()->create(['user_id' => $user->id, 'community_id' => $this->community->id]);

    $request = ProviderServiceRequest::factory()->pending()->create([
        'community_id' => $this->community->id,
        'resident_id' => $resident->id,
    ]);

    $response = $this->actingAs($user)->patchJson("http://{$this->domain}/api/ecosystem/service-requests/{$request->id}/cancel");

    $response->assertOk();
    $response->assertJsonPath('data.status', ServiceRequestStatus::CANCELLED->value);

    $this->assertDatabaseHas('provider_service_requests', [
        'id' => $request->id,
        'status' => ServiceRequestStatus::CANCELLED->value,
    ]);
});

it('prevents cancelling if not pending', function () {
    $user = User::factory()->create();
    $user->communities()->attach($this->community, ['role' => CommunityRole::Resident->value]);
    $resident = Resident::factory()->create(['user_id' => $user->id, 'community_id' => $this->community->id]);

    $request = ProviderServiceRequest::factory()->create([
        'community_id' => $this->community->id,
        'resident_id' => $resident->id,
        'status' => ServiceRequestStatus::ACCEPTED,
    ]);

    $response = $this->actingAs($user)->patchJson("http://{$this->domain}/api/ecosystem/service-requests/{$request->id}/cancel");
    $response->assertForbidden(); // Policy prevents update if not pending OR we return exception from Action. The policy blocks it.
});
