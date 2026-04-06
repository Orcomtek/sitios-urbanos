<?php

use App\Models\Community;
use App\Models\User;

it('redirects to the community subdomain if valid', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['slug' => 'valid-comm']);
    $user->communities()->attach($community->id, ['role' => 'resident']);

    $response = $this->actingAs($user)->get(route('communities.enter', 'valid-comm'));

    $centralDomain = config('app.central_domain');
    $response->assertRedirect('http://valid-comm.' . $centralDomain);
});

it('returns 404 if the user does not belong to the community', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['slug' => 'secret-comm']);

    $response = $this->actingAs($user)->get(route('communities.enter', 'secret-comm'));

    $response->assertNotFound();
});

it('returns 404 if the community does not exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('communities.enter', 'fake-comm'));

    $response->assertNotFound();
});

it('returns 404 if the community is inactive', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create([
        'slug' => 'inactive-comm',
        'status' => 'inactive',
    ]);
    
    $user->communities()->attach($community->id, ['role' => 'resident']);

    $response = $this->actingAs($user)->get(route('communities.enter', 'inactive-comm'));

    $response->assertNotFound();
});
