<?php

use App\Models\LegalConsent;
use App\Models\User;
use App\Models\Community;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a legal consent record', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    
    $consent = LegalConsent::create([
        'user_id' => $user->id,
        'community_id' => $community->id,
        'consent_type' => 'privacy_policy',
        'version' => '1.0.0',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest/TestRunner',
    ]);
    
    expect($consent->id)->not->toBeNull()
        ->and($consent->consent_type)->toBe('privacy_policy')
        ->and($consent->agreed_at)->not->toBeNull();
});
