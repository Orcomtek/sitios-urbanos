<?php

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\Governance\Announcement;
use App\Models\Governance\Document;
use App\Models\Governance\Poll;
use App\Models\Governance\PollVote;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use App\Services\TenantContext;

it('allows admin to create an announcement but denies residents', function () {
    $community = Community::factory()->create();
    app(TenantContext::class)->set($community);

    $admin = User::factory()->create();
    $community->users()->attach($admin, ['role' => CommunityRole::Admin->value]);

    $residentUser = User::factory()->create();
    $community->users()->attach($residentUser, ['role' => CommunityRole::Resident->value]);

    $data = [
        'title' => 'Important Maintenance',
        'content' => 'Water will be cut off tomorrow.',
        'type' => 'urgent',
    ];

    // Resident tries and fails
    $url = route('api.governance.announcements.store', ['community_slug' => $community->slug]);
    $this->actingAs($residentUser, 'sanctum')->postJson($url, $data)->assertStatus(403);

    // Admin tries and succeeds
    $this->actingAs($admin, 'sanctum')->postJson($url, $data)->assertStatus(201);

    expect(Announcement::where('community_id', $community->id)->count())->toBe(1);
});

it('prevents cross-tenant document creation and retrieval', function () {
    $community1 = Community::factory()->create();
    $community2 = Community::factory()->create();

    $admin1 = User::factory()->create();
    $community1->users()->attach($admin1, ['role' => CommunityRole::Admin->value]);

    app(TenantContext::class)->set($community1);

    // Create a document in community1
    $data = [
        'title' => 'House Rules',
        'document_type' => 'regulation',
        'file_url' => 'https://example.com/rules.pdf',
    ];

    $url1 = route('api.governance.documents.store', ['community_slug' => $community1->slug]);
    $this->actingAs($admin1, 'sanctum')->postJson($url1, $data)->assertStatus(201);

    // Fetch documents in community2 with a valid user
    $admin2 = User::factory()->create();
    $community2->users()->attach($admin2, ['role' => CommunityRole::Admin->value]);
    app(TenantContext::class)->set($community2);

    $url2 = route('api.governance.documents.index', ['community_slug' => $community2->slug]);
    $response = $this->actingAs($admin2, 'sanctum')->getJson($url2);

    $response->assertStatus(200)->assertJsonCount(0, 'data');
});

it('allows residents to vote but enforces one vote per unit', function () {
    $community = Community::factory()->create();
    app(TenantContext::class)->set($community);

    $admin = User::factory()->create();
    $community->users()->attach($admin, ['role' => CommunityRole::Admin->value]);

    $unit = Unit::factory()->create(['community_id' => $community->id]);

    // User 1 related to unit
    $residentUser1 = User::factory()->create();
    $community->users()->attach($residentUser1, ['role' => CommunityRole::Resident->value]);
    Resident::factory()->create(['community_id' => $community->id, 'unit_id' => $unit->id, 'user_id' => $residentUser1->id, 'is_active' => true]);

    // User 2 related to unit
    $residentUser2 = User::factory()->create();
    $community->users()->attach($residentUser2, ['role' => CommunityRole::Resident->value]);
    Resident::factory()->create(['community_id' => $community->id, 'unit_id' => $unit->id, 'user_id' => $residentUser2->id, 'is_active' => true]);

    // Create a poll
    $poll = Poll::create([
        'community_id' => $community->id,
        'title' => 'Color of the building',
        'created_by' => $admin->id,
        'status' => 'open',
    ]);
    $optionA = $poll->options()->create(['text' => 'Red']);
    $optionB = $poll->options()->create(['text' => 'Blue']);

    // User 1 votes
    $url = route('api.governance.polls.vote', ['community_slug' => $community->slug, 'poll' => $poll->id]);
    $response1 = $this->actingAs($residentUser1, 'sanctum')->postJson($url, [
        'unit_id' => $unit->id,
        'poll_option_id' => $optionA->id,
    ]);
    $response1->assertStatus(200);

    // User 2 tries to vote for the same unit and it must fail
    $response2 = $this->actingAs($residentUser2, 'sanctum')->postJson($url, [
        'unit_id' => $unit->id,
        'poll_option_id' => $optionB->id,
    ]);
    $response2->assertStatus(422)
        ->assertJsonValidationErrors(['unit_id']);

    // Verify only one vote exists
    expect(PollVote::where('poll_id', $poll->id)->count())->toBe(1);
    expect(PollVote::first()->poll_option_id)->toBe($optionA->id);
});

it('rejects votes when the poll is closed', function () {
    $community = Community::factory()->create();
    app(TenantContext::class)->set($community);

    $admin = User::factory()->create();

    $unit = Unit::factory()->create(['community_id' => $community->id]);
    $residentUser = User::factory()->create();
    $community->users()->attach($residentUser, ['role' => CommunityRole::Resident->value]);
    Resident::factory()->create(['community_id' => $community->id, 'unit_id' => $unit->id, 'user_id' => $residentUser->id, 'is_active' => true]);

    $poll = Poll::create([
        'community_id' => $community->id,
        'title' => 'Test Poll',
        'created_by' => $admin->id,
        'status' => 'closed',
    ]);
    $optionA = $poll->options()->create(['text' => 'A']);

    $url = route('api.governance.polls.vote', ['community_slug' => $community->slug, 'poll' => $poll->id]);
    $response = $this->actingAs($residentUser, 'sanctum')->postJson($url, [
        'unit_id' => $unit->id,
        'poll_option_id' => $optionA->id,
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['poll']);
});
