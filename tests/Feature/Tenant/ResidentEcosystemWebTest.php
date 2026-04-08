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

