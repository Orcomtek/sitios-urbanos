<?php

use App\Models\Community;
use App\Models\Resident;
use App\Models\SecurityLog;
use App\Models\Unit;
use App\Services\Privacy\RetentionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cleans old non-critical logs', function () {
    $community = Community::factory()->create();

    // Create an old log
    $oldLog = SecurityLog::create([
        'community_id' => $community->id,
        'action' => 'test.old',
        'created_at' => now()->subMonths(61), // older than 60 months (5 years)
    ]);

    // Create a new log
    $newLog = SecurityLog::create([
        'community_id' => $community->id,
        'action' => 'test.new',
        'created_at' => now()->subMonths(10), // younger
    ]);

    $manager = app(RetentionManager::class);
    $deletedCount = $manager->cleanOldLogs();

    expect($deletedCount)->toBe(1)
        ->and(SecurityLog::find($oldLog->id))->toBeNull()
        ->and(SecurityLog::find($newLog->id))->not->toBeNull();
});

it('anonymizes deleted residents', function () {
    $community = Community::factory()->create();
    $unit = Unit::factory()->create(['community_id' => $community->id]);

    // Create an old soft-deleted resident
    $resident = Resident::factory()->create([
        'community_id' => $community->id,
        'unit_id' => $unit->id,
        'email' => 'to_be_anonymized@example.com',
        'deleted_at' => now()->subMonths(7), // older than 6 months
    ]);

    $manager = app(RetentionManager::class);
    $anonymizedCount = $manager->anonymizeDeletedResidents();

    expect($anonymizedCount)->toBe(1);

    $resident->refresh();
    expect($resident->email)->toBeNull()
        ->and($resident->full_name)->toStartWith('Anonymizado - ');
});
