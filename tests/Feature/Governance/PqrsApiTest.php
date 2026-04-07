<?php

use App\Enums\CommunityRole;
use App\Enums\PqrsStatus;
use App\Enums\PqrsType;
use App\Models\Community;
use App\Models\Pqrs;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\Governance\PqrsCreatedNotification;
use App\Notifications\Governance\PqrsUpdatedNotification;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Notification;

it('allows residents to list their own pqrs', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $resident = Resident::factory()->create(['community_id' => $community->id, 'user_id' => $user->id]);

    $community->users()->attach($user, ['role' => CommunityRole::Resident->value]);

    app(TenantContext::class)->set($community);

    $pqrs1 = Pqrs::factory()->create(['community_id' => $community->id, 'resident_id' => $resident->id, 'subject' => 'My first issue']);

    // Someone else's PQRS
    $otherResident = Resident::factory()->create(['community_id' => $community->id]);
    Pqrs::factory()->create(['community_id' => $community->id, 'resident_id' => $otherResident->id, 'subject' => 'Other issue']);

    $url = route('api.governance.pqrs.index', ['community_slug' => $community->slug]);
    $response = $this->actingAs($user, 'sanctum')->getJson($url);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.subject', 'My first issue');
});

it('allows admins to list all pqrs in the community', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $community->users()->attach($user, ['role' => CommunityRole::Admin->value]);

    app(TenantContext::class)->set($community);

    $resident1 = Resident::factory()->create(['community_id' => $community->id]);
    $resident2 = Resident::factory()->create(['community_id' => $community->id]);

    Pqrs::factory()->create(['community_id' => $community->id, 'resident_id' => $resident1->id]);
    Pqrs::factory()->create(['community_id' => $community->id, 'resident_id' => $resident2->id]);

    $url = route('api.governance.pqrs.index', ['community_slug' => $community->slug]);
    $response = $this->actingAs($user, 'sanctum')->getJson($url);

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

it('creates a pqrs and notifies admins', function () {
    Notification::fake();

    $community = Community::factory()->create();
    $user = User::factory()->create();
    $resident = Resident::factory()->create(['community_id' => $community->id, 'user_id' => $user->id]);
    $community->users()->attach($user, ['role' => CommunityRole::Resident->value]);

    $adminUser = User::factory()->create();
    $community->users()->attach($adminUser, ['role' => CommunityRole::Admin->value]);

    app(TenantContext::class)->set($community);

    $data = [
        'type' => PqrsType::COMPLAINT->value,
        'subject' => 'Noise issue',
        'description' => 'Loud music at 3 AM.',
        'is_anonymous' => true,
    ];

    $url = route('api.governance.pqrs.store', ['community_slug' => $community->slug]);
    $response = $this->actingAs($user, 'sanctum')->postJson($url, $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.subject', 'Noise issue');

    Notification::assertSentTo(
        [$adminUser], PqrsCreatedNotification::class
    );
});

it('hides resident information from the api response when anonymous and user is admin', function () {
    $community = Community::factory()->create();
    $adminUser = User::factory()->create();
    $community->users()->attach($adminUser, ['role' => CommunityRole::Admin->value]);

    $resident = Resident::factory()->create(['community_id' => $community->id]);

    app(TenantContext::class)->set($community);

    $pqrs = Pqrs::factory()->create([
        'community_id' => $community->id,
        'resident_id' => $resident->id,
        'is_anonymous' => true,
    ]);

    $url = route('api.governance.pqrs.show', ['community_slug' => $community->slug, 'pqrs' => $pqrs->id]);
    $response = $this->actingAs($adminUser, 'sanctum')->getJson($url);

    $response->assertStatus(200)
        ->assertJsonMissing(['resident_id'])
        ->assertJsonMissing(['resident']);
});

it('allows admin to respond to a pqrs and notifies the resident via linked user', function () {
    Notification::fake();

    $community = Community::factory()->create();

    $residentUser = User::factory()->create();
    $resident = Resident::factory()->create(['community_id' => $community->id, 'user_id' => $residentUser->id]);
    $community->users()->attach($residentUser, ['role' => CommunityRole::Resident->value]);

    $adminUser = User::factory()->create();
    $community->users()->attach($adminUser, ['role' => CommunityRole::Admin->value]);

    app(TenantContext::class)->set($community);

    $pqrs = Pqrs::factory()->create([
        'community_id' => $community->id,
        'resident_id' => $resident->id,
        'status' => PqrsStatus::OPEN,
    ]);

    $data = [
        'status' => PqrsStatus::CLOSED->value,
        'admin_response' => 'Will be resolved today.',
    ];

    $url = route('api.governance.pqrs.update_status', ['community_slug' => $community->slug, 'pqrs' => $pqrs->id]);
    $response = $this->actingAs($adminUser, 'sanctum')->patchJson($url, $data);

    $response->assertStatus(200)
        ->assertJsonPath('data.status', PqrsStatus::CLOSED->value);

    Notification::assertSentTo(
        [$residentUser], PqrsUpdatedNotification::class
    );
});
