<?php

declare(strict_types=1);

use App\Actions\ResolveUserCommunityBySlugAction;
use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('resolves active community attached to user', function () {
    // Arrange
    $user = User::factory()->create();
    $community = Community::factory()->create([
        'slug' => 'active-community',
        'status' => 'active',
    ]);

    // Attach user to community
    $user->communities()->attach($community->id, ['role' => 'resident']);

    $action = new ResolveUserCommunityBySlugAction;

    // Act
    $resolved = $action->execute($user, 'active-community');

    // Assert
    expect($resolved->id)->toBe($community->id);
});

it('throws exception if community slug does not exist', function () {
    // Arrange
    $user = User::factory()->create();
    $action = new ResolveUserCommunityBySlugAction;

    // Act & Assert
    expect(fn () => $action->execute($user, 'non-existent-slug'))
        ->toThrow(ModelNotFoundException::class);
});

it('throws exception if community exists but user is not attached', function () {
    // Arrange
    $user = User::factory()->create();
    $community = Community::factory()->create([
        'slug' => 'unattached-community',
        'status' => 'active',
    ]);

    $action = new ResolveUserCommunityBySlugAction;

    // Act & Assert
    expect(fn () => $action->execute($user, 'unattached-community'))
        ->toThrow(ModelNotFoundException::class);
});

it('throws exception if community is attached but inactive', function () {
    // Arrange
    $user = User::factory()->create();
    $community = Community::factory()->create([
        'slug' => 'inactive-community',
        'status' => 'inactive',
    ]);

    // Attach user to community
    $user->communities()->attach($community->id, ['role' => 'resident']);

    $action = new ResolveUserCommunityBySlugAction;

    // Act & Assert
    expect(fn () => $action->execute($user, 'inactive-community'))
        ->toThrow(ModelNotFoundException::class);
});
