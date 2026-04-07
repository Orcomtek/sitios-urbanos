<?php

use App\Actions\Privacy\AnonymizeResidentAction;
use App\Models\Community;
use App\Models\Resident;
use App\Models\SecurityLog;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('anonymizes a resident securely and logs it', function () {
    $community = Community::factory()->create();
    $unit = Unit::factory()->create(['community_id' => $community->id]);
    $user = User::factory()->create();

    $resident = Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'user_id' => $user->id,
        'full_name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+1234567890',
        'is_active' => true,
    ]);

    $action = app(AnonymizeResidentAction::class);
    $anonymized = $action->execute($resident, $user);

    expect($anonymized->full_name)->toStartWith('Anonymizado - ')
        ->and($anonymized->email)->toBeNull()
        ->and($anonymized->phone)->toBeNull()
        ->and($anonymized->user_id)->toBeNull()
        ->and($anonymized->is_active)->toBeFalse();

    $log = SecurityLog::where('community_id', $community->id)->first();
    expect($log)->not->toBeNull()
        ->and($log->action)->toBe('resident.anonymized')
        ->and($log->actor_id)->toBe($user->id);
});
