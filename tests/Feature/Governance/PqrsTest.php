<?php

use App\Actions\Governance\UpdatePqrsStateAction;
use App\Enums\PqrsStatus;
use App\Enums\PqrsType;
use App\Models\Community;
use App\Models\Pqrs;
use App\Models\Resident;
use App\Models\User;
use App\Services\TenantContext;

use function Pest\Laravel\assertDatabaseHas;

it('creates a pqrs scoped to a tenant', function () {
    $community = Community::factory()->create();
    $resident = Resident::factory()->create(['community_id' => $community->id]);

    app(TenantContext::class)->set($community);

    $pqrs = Pqrs::factory()->create([
        'community_id' => $community->id,
        'resident_id' => $resident->id,
        'type' => PqrsType::COMPLAINT,
        'subject' => 'Noise issue',
    ]);

    expect($pqrs->community_id)->toBe($community->id)
        ->and($pqrs->type)->toBe(PqrsType::COMPLAINT);

    assertDatabaseHas('pqrs', [
        'id' => $pqrs->id,
        'community_id' => $community->id,
        'subject' => 'Noise issue',
    ]);
});

it('cannot query pqrs from another tenant', function () {
    $community1 = Community::factory()->create();
    $community2 = Community::factory()->create();

    $resident1 = Resident::factory()->create(['community_id' => $community1->id]);
    Pqrs::factory()->create(['community_id' => $community1->id, 'resident_id' => $resident1->id]);

    app(TenantContext::class)->set($community2);

    expect(Pqrs::count())->toBe(0);
});

it('hides resident information when pqrs is anonymous', function () {
    $community = Community::factory()->create();
    $resident = Resident::factory()->create(['community_id' => $community->id]);

    $pqrs = Pqrs::factory()->create([
        'community_id' => $community->id,
        'resident_id' => $resident->id,
        'is_anonymous' => true,
    ]);

    // Ensure we load relation to check the overriding logic
    $pqrs->load('resident');

    $array = $pqrs->toArray();

    expect($array)->not->toHaveKey('resident_id')
        ->and($array)->not->toHaveKey('resident');

    // Internally, it still has the value
    expect($pqrs->resident_id)->toBe($resident->id);
});

it('does not hide resident information when pqrs is not anonymous', function () {
    $community = Community::factory()->create();
    $resident = Resident::factory()->create(['community_id' => $community->id]);

    $pqrs = Pqrs::factory()->create([
        'community_id' => $community->id,
        'resident_id' => $resident->id,
        'is_anonymous' => false,
    ]);

    $pqrs->load('resident');

    $array = $pqrs->toArray();

    expect($array)->toHaveKey('resident_id')
        ->and($array['resident_id'])->toBe($resident->id)
        ->and($array)->toHaveKey('resident');
});

it('updates pqrs state correctly and logs the transition', function () {
    $community = Community::factory()->create();
    $resident = Resident::factory()->create(['community_id' => $community->id]);
    $adminUser = User::factory()->create();

    app(TenantContext::class)->set($community);

    $pqrs = Pqrs::factory()->create([
        'community_id' => $community->id,
        'resident_id' => $resident->id,
        'status' => PqrsStatus::OPEN,
    ]);

    $action = new UpdatePqrsStateAction;
    $action->execute($pqrs, PqrsStatus::CLOSED, $adminUser, 'This is unresolvable');

    $pqrs->refresh();

    expect($pqrs->status)->toBe(PqrsStatus::CLOSED)
        ->and($pqrs->admin_response)->toBe('This is unresolvable')
        ->and($pqrs->responded_at)->not->toBeNull()
        ->and($pqrs->closed_at)->not->toBeNull();

    assertDatabaseHas('security_logs', [
        'community_id' => $community->id,
        'actor_id' => $adminUser->id,
        'action' => 'pqrs.status_updated',
        'subject_id' => $pqrs->id,
        'subject_type' => Pqrs::class,
    ]);
});
