<?php

use App\Actions\ResolveUserCommunityBySlugAction;
use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('resolves a community when the user belongs to it and it is active', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $user->communities()->attach($community, ['role' => 'admin']);

    $action = app(ResolveUserCommunityBySlugAction::class);
    $resolved = $action->execute($user, $community->slug);

    expect($resolved->id)->toBe($community->id);
});

it('throws ModelNotFoundException when the community slug does not exist', function () {
    $user = User::factory()->create();

    $action = app(ResolveUserCommunityBySlugAction::class);

    expect(fn () => $action->execute($user, 'non-existent-slug'))
        ->toThrow(ModelNotFoundException::class);
});

it('throws ModelNotFoundException when the user is not attached to the community', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    // user is not attached to community

    $action = app(ResolveUserCommunityBySlugAction::class);

    expect(fn () => $action->execute($user, $community->slug))
        ->toThrow(ModelNotFoundException::class);
});

it('throws ModelNotFoundException when the user is attached but the community is inactive', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'inactive']); // assuming status is string based on migration
    $user->communities()->attach($community, ['role' => 'admin']);

    $action = app(ResolveUserCommunityBySlugAction::class);

    expect(fn () => $action->execute($user, $community->slug))
        ->toThrow(ModelNotFoundException::class);
});
