<?php

use App\Actions\ResolveUserCommunityBySlugAction;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

it('ensures controllers do not bypass middleware for tenant resolution')
    ->expect('App\Http\Controllers')
    ->not->toUse(ResolveUserCommunityBySlugAction::class);

it('ensures global and boundary models do not contain a community_id column', function () {
    expect(Schema::hasColumn('users', 'community_id'))->toBeFalse('The users table must not have a community_id column. It is a global model.');
    expect(Schema::hasColumn('communities', 'community_id'))->toBeFalse('The communities table must not have a community_id column. It is a boundary model.');
});
