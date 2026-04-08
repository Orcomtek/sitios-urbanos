<?php

use App\Models\Community;
use App\Models\EmergencyEvent;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\Security\EmergencyEventTriggeredNotification;

beforeEach(function () {
    $this->community = Community::factory()->create();
    $this->user = User::factory()->create();

    // Attach user to community
    $this->community->users()->attach($this->user, ['role' => 'resident']);

    $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);

    Resident::factory()->create([
        'community_id' => $this->community->id,
        'unit_id' => $this->unit->id,
        'user_id' => $this->user->id,
        'is_active' => true,
    ]);
});

it('lists only notifications for the current tenant', function () {
    $otherCommunity = Community::factory()->create();
    $otherCommunity->users()->attach($this->user, ['role' => 'resident']);

    $emergency1 = EmergencyEvent::factory()->create(['community_id' => $this->community->id, 'unit_id' => $this->unit->id]);
    $emergency2 = EmergencyEvent::factory()->create(['community_id' => $otherCommunity->id]);

    $this->user->notify(new EmergencyEventTriggeredNotification($emergency1));
    $this->user->notify(new EmergencyEventTriggeredNotification($emergency2));

    $response = $this->actingAs($this->user)->getJson(route('api.cockpit.notifications.index', ['community_slug' => $this->community->slug]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.data.community_id', $this->community->id);
});

it('can mark a notification as read and prevents reading cross-tenant notifications', function () {
    $otherCommunity = Community::factory()->create();
    $otherCommunity->users()->attach($this->user, ['role' => 'resident']);

    $emergency1 = EmergencyEvent::factory()->create(['community_id' => $this->community->id, 'unit_id' => $this->unit->id]);
    $emergency2 = EmergencyEvent::factory()->create(['community_id' => $otherCommunity->id]);

    $this->user->notify(new EmergencyEventTriggeredNotification($emergency1));
    $this->user->notify(new EmergencyEventTriggeredNotification($emergency2));

    $notification1 = $this->user->notifications()->where('data->community_id', $this->community->id)->first();
    $notification2 = $this->user->notifications()->where('data->community_id', $otherCommunity->id)->first();

    // Valid mark as read
    $response = $this->actingAs($this->user)->patchJson(route('api.cockpit.notifications.read', [
        'community_slug' => $this->community->slug,
        'id' => $notification1->id,
    ]));

    $response->assertOk();
    expect($notification1->fresh()->read_at)->not->toBeNull();

    // Invalid mark as read (cross-tenant)
    $response2 = $this->actingAs($this->user)->patchJson(route('api.cockpit.notifications.read', [
        'community_slug' => $this->community->slug,
        'id' => $notification2->id,
    ]));

    $response2->assertNotFound();
    expect($notification2->fresh()->read_at)->toBeNull();
});

it('can mark all notifications as read for current tenant only', function () {
    $otherCommunity = Community::factory()->create();
    $otherCommunity->users()->attach($this->user, ['role' => 'resident']);

    $emergency1 = EmergencyEvent::factory()->create(['community_id' => $this->community->id, 'unit_id' => $this->unit->id]);
    $emergency2 = EmergencyEvent::factory()->create(['community_id' => $otherCommunity->id]);

    $this->user->notify(new EmergencyEventTriggeredNotification($emergency1));
    $this->user->notify(new EmergencyEventTriggeredNotification($emergency2));

    $response = $this->actingAs($this->user)->patchJson(route('api.cockpit.notifications.read-all', ['community_slug' => $this->community->slug]));
    $response->assertOk();

    $notification1 = $this->user->notifications()->where('data->community_id', $this->community->id)->first();
    $notification2 = $this->user->notifications()->where('data->community_id', $otherCommunity->id)->first();

    expect($notification1->read_at)->not->toBeNull();
    expect($notification2->read_at)->toBeNull();
});
