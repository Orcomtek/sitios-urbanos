<?php

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\Resident;
use App\Models\User;

it('allows residents to access the ecosystem cockpit', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $user->communities()->attach($community, ['role' => CommunityRole::Resident]);

    Resident::factory()->create([
        'community_id' => $community->id,
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    $centralDomain = config('app.central_domain');

    $this->withoutVite();
    
    $response = $this->actingAs($user)
        ->get("http://{$community->slug}.{$centralDomain}/cockpit/resident/ecosystem");

    $response->assertOk();
});

it('forbids admins from accessing the ecosystem cockpit', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $user->communities()->attach($community, ['role' => CommunityRole::Admin]);

    $centralDomain = config('app.central_domain');

    $response = $this->actingAs($user)
        ->get("http://{$community->slug}.{$centralDomain}/cockpit/resident/ecosystem");

    $response->assertForbidden();
});

it('forbids guards from accessing the ecosystem cockpit', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $user->communities()->attach($community, ['role' => CommunityRole::Guard]);

    $centralDomain = config('app.central_domain');

    $response = $this->actingAs($user)
        ->get("http://{$community->slug}.{$centralDomain}/cockpit/resident/ecosystem");

    $response->assertForbidden();
});

it('allows residents to create a listing', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $user->communities()->attach($community, ['role' => CommunityRole::Resident]);

    $resident = Resident::factory()->create([
        'community_id' => $community->id,
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    $centralDomain = config('app.central_domain');

    $response = $this->actingAs($user)
        ->postJson("http://{$community->slug}.{$centralDomain}/api/ecosystem/listings", [
            'title' => 'Bicicleta en buen estado',
            'description' => 'Vendo bicicleta de ruta en perfecto estado.',
            'category' => \App\Enums\ListingCategory::Items->value,
            'price' => 120000,
            'show_contact_info' => true,
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('listings', [
        'title' => 'Bicicleta en buen estado',
        'resident_id' => $resident->id,
    ]);
});

it('allows residents to edit only their own listing', function () {
    $community = Community::factory()->create();
    
    // User 1
    $user1 = User::factory()->create();
    $user1->communities()->attach($community, ['role' => CommunityRole::Resident]);
    $resident1 = Resident::factory()->create(['community_id' => $community->id, 'user_id' => $user1->id, 'is_active' => true]);
    
    // User 2
    $user2 = User::factory()->create();
    $user2->communities()->attach($community, ['role' => CommunityRole::Resident]);
    $resident2 = Resident::factory()->create(['community_id' => $community->id, 'user_id' => $user2->id, 'is_active' => true]);

    $listing = \App\Models\Listing::factory()->create([
        'community_id' => $community->id,
        'resident_id' => $resident1->id,
    ]);

    $centralDomain = config('app.central_domain');

    // Resident 2 tries to edit Resident 1's listing (Should Fail)
    $response = $this->actingAs($user2)
        ->patchJson("http://{$community->slug}.{$centralDomain}/api/ecosystem/listings/{$listing->id}", [
            'status' => \App\Enums\ListingStatus::Paused->value,
        ]);
    $response->assertForbidden();

    // Resident 1 edits their own listing (Should Succeed)
    $response = $this->actingAs($user1)
        ->patchJson("http://{$community->slug}.{$centralDomain}/api/ecosystem/listings/{$listing->id}", [
            'status' => \App\Enums\ListingStatus::Paused->value,
        ]);
    $response->assertOk();
    
    $this->assertDatabaseHas('listings', [
        'id' => $listing->id,
        'status' => \App\Enums\ListingStatus::Paused->value,
    ]);
});

it('correctly filters active listings and hides contact info', function () {
    $community = Community::factory()->create();
    $user = User::factory()->create();
    $user->communities()->attach($community, ['role' => CommunityRole::Resident]);
    $resident = Resident::factory()->create(['community_id' => $community->id, 'user_id' => $user->id, 'is_active' => true]);

    $user2 = User::factory()->create();
    $user2->communities()->attach($community, ['role' => CommunityRole::Resident]);
    $resident2 = Resident::factory()->create(['community_id' => $community->id, 'user_id' => $user2->id, 'is_active' => true, 'phone' => '123456789']);

    // Active listing with hidden contact info
    \App\Models\Listing::factory()->create([
        'community_id' => $community->id,
        'resident_id' => $resident2->id,
        'status' => \App\Enums\ListingStatus::Active,
        'show_contact_info' => false,
    ]);

    // Paused listing 
    \App\Models\Listing::factory()->create([
        'community_id' => $community->id,
        'resident_id' => $resident2->id,
        'status' => \App\Enums\ListingStatus::Paused,
    ]);

    $centralDomain = config('app.central_domain');

    $response = $this->actingAs($user)
        ->getJson("http://{$community->slug}.{$centralDomain}/api/ecosystem/listings");

    $response->assertOk();
    
    // Only 1 listing should be visible to Resident 1 (the Active one from User 2)
    // The Paused one belongs to User 2, so it's hidden from User 1.
    $response->assertJsonCount(1, 'data');
    
    $data = $response->json('data')[0];
    expect($data['resident']['phone'])->toBeNull(); // Phone must be obfuscated
});
