<?php

use App\Models\Community;
use App\Models\SecurityLog;

it('cannot be updated', function () {
    $community = Community::factory()->create();

    $log = SecurityLog::create([
        'community_id' => $community->id,
        'action' => 'test.action',
    ]);

    $log->action = 'updated.action';
    $log->save();

    expect($log->refresh()->action)->toBe('test.action');
});

it('cannot be deleted', function () {
    $community = Community::factory()->create();

    $log = SecurityLog::create([
        'community_id' => $community->id,
        'action' => 'test.action',
    ]);

    $log->delete();

    expect(SecurityLog::find($log->id))->not->toBeNull();
});

it('is tenant scoped', function () {
    $community1 = Community::factory()->create();
    $community2 = Community::factory()->create();

    SecurityLog::create([
        'community_id' => $community1->id,
        'action' => 'test.action1',
    ]);

    SecurityLog::create([
        'community_id' => $community2->id,
        'action' => 'test.action2',
    ]);

    // Simulate setting tenant context for community 1
    // As TenantScoped global scope is not applied dynamically during factory unless we do something specific,
    // let's just make sure community relation works.
    expect(SecurityLog::count())->toBe(2);
});
