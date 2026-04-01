<?php

use App\Models\Community;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects unauthenticated users to the login page', function () {
    $response = $this->get('/comunidades');

    $response->assertRedirect('/login');
});

it('displays only the communities assigned to the authenticated user', function () {
    $user = User::factory()->create();

    $community1 = Community::factory()->create();
    $community2 = Community::factory()->create();
    $otherCommunity = Community::factory()->create();

    $user->communities()->attach([
        $community1->id => ['role' => 'resident'],
        $community2->id => ['role' => 'admin'],
    ]);

    $response = $this->actingAs($user)->get('/comunidades');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Communities/Index')
            ->has('communities', 2)
            ->where('communities.0.id', $community1->id)
            ->where('communities.1.id', $community2->id)
        );
});

it('does not display communities assigned to other users', function () {
    $userA = User::factory()->create();
    $communityA = Community::factory()->create();
    $userA->communities()->attach($communityA->id, ['role' => 'resident']);

    $userB = User::factory()->create();
    $communityB = Community::factory()->create();
    $userB->communities()->attach($communityB->id, ['role' => 'resident']);

    $response = $this->actingAs($userA)->get('/comunidades');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Communities/Index')
            ->has('communities', 1)
            ->where('communities.0.id', $communityA->id)
        );
});
