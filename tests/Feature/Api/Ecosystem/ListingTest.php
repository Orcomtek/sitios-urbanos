<?php

declare(strict_types=1);

use App\Enums\CommunityRole;
use App\Enums\ListingCategory;
use App\Enums\ListingStatus;
use App\Models\Community;
use App\Models\Listing;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use App\Services\TenantContext;

beforeEach(function () {
    $this->community = Community::factory()->create();
    app(TenantContext::class)->set($this->community);

    $this->unit = Unit::factory()->create(['community_id' => $this->community->id]);

    $this->user = User::factory()->create();
    $this->user->communities()->attach($this->community->id, ['role' => CommunityRole::Resident->value, 'unit_id' => $this->unit->id]);

    $this->resident = Resident::factory()->create([
        'user_id' => $this->user->id,
        'unit_id' => $this->unit->id,
        'community_id' => $this->community->id,
        'is_active' => true,
    ]);
});

it('lists active listings for residents', function () {
    Listing::factory()->count(3)->create([
        'community_id' => $this->community->id,
        'status' => ListingStatus::Active,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.ecosystem.listings.index', ['community_slug' => $this->community->slug]));

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('hides contact info when show_contact_info is false', function () {
    $listing = Listing::factory()->create([
        'community_id' => $this->community->id,
        'resident_id' => $this->resident->id,
        'show_contact_info' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.ecosystem.listings.show', ['community_slug' => $this->community->slug, 'listing' => $listing->id]));

    $response->assertStatus(200)
        ->assertJsonMissing(['email' => $this->resident->email])
        ->assertJsonMissing(['phone' => $this->resident->phone]);
});

it('shows contact info when show_contact_info is true', function () {
    $listing = Listing::factory()->create([
        'community_id' => $this->community->id,
        'resident_id' => $this->resident->id,
        'show_contact_info' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.ecosystem.listings.show', ['community_slug' => $this->community->slug, 'listing' => $listing->id]));

    $response->assertStatus(200)
        ->assertJsonFragment([
            'email' => $this->resident->email,
            'phone' => $this->resident->phone,
        ]);
});

it('allows residents to create a listing', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('api.ecosystem.listings.store', ['community_slug' => $this->community->slug]), [
            'title' => 'My Item',
            'description' => 'For sale',
            'price' => 100,
            'category' => ListingCategory::Items->value,
            'show_contact_info' => true,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'My Item');

    $this->assertDatabaseHas('listings', [
        'title' => 'My Item',
        'resident_id' => $this->resident->id,
        'status' => ListingStatus::Active->value,
    ]);
});

it('ignores malicious resident_id on creation', function () {
    $otherResident = Resident::factory()->create(['community_id' => $this->community->id]);

    $response = $this->actingAs($this->user)
        ->postJson(route('api.ecosystem.listings.store', ['community_slug' => $this->community->slug]), [
            'resident_id' => $otherResident->id,
            'title' => 'My Item',
            'description' => 'For sale',
            'category' => ListingCategory::Items->value,
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('listings', [
        'title' => 'My Item',
        'resident_id' => $this->resident->id,
    ]);
});

it('allows residents to update their own listing', function () {
    $listing = Listing::factory()->create([
        'community_id' => $this->community->id,
        'resident_id' => $this->resident->id,
    ]);

    $response = $this->actingAs($this->user)
        ->patchJson(route('api.ecosystem.listings.update', ['community_slug' => $this->community->slug, 'listing' => $listing->id]), [
            'title' => 'Updated Title',
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('listings', [
        'id' => $listing->id,
        'title' => 'Updated Title',
    ]);
});

it('prevents residents from updating others listings', function () {
    $otherResident = Resident::factory()->create(['community_id' => $this->community->id]);
    $listing = Listing::factory()->create([
        'community_id' => $this->community->id,
        'resident_id' => $otherResident->id,
    ]);

    $response = $this->actingAs($this->user)
        ->patchJson(route('api.ecosystem.listings.update', ['community_slug' => $this->community->slug, 'listing' => $listing->id]), [
            'title' => 'Updated Title',
        ]);

    $response->assertStatus(403);
});

it('allows admins to moderate listings', function () {
    $adminUser = User::factory()->create();
    $adminUser->communities()->attach($this->community->id, ['role' => CommunityRole::Admin->value]);

    $listing = Listing::factory()->create([
        'community_id' => $this->community->id,
        'status' => ListingStatus::Active,
    ]);

    $response = $this->actingAs($adminUser)
        ->patchJson(route('api.ecosystem.listings.moderate', ['community_slug' => $this->community->slug, 'listing' => $listing->id]), [
            'status' => ListingStatus::Reported->value,
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('listings', [
        'id' => $listing->id,
        'status' => ListingStatus::Reported->value,
    ]);
});

it('prevents guards from accessing listings', function () {
    $guardUser = User::factory()->create();
    $guardUser->communities()->attach($this->community->id, ['role' => CommunityRole::Guard->value]);

    $response = $this->actingAs($guardUser)
        ->getJson(route('api.ecosystem.listings.index', ['community_slug' => $this->community->slug]));

    $response->assertStatus(403);
});

it('ensures listings are tenant scoped', function () {
    $otherCommunity = Community::factory()->create();

    $listing = Listing::factory()->create([
        'community_id' => $otherCommunity->id,
        'status' => ListingStatus::Active,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('api.ecosystem.listings.index', ['community_slug' => $this->community->slug]));

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});
