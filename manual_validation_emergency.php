<?php

use App\Models\Community;
use App\Models\Unit;
use App\Models\User;
use App\Models\Resident;
use App\Actions\Security\TriggerEmergencyEventAction;

// Get a random community that has users
$community = Community::first();
if (!$community) {
    echo "No communities found\n";
    exit;
}

$unit = Unit::where('community_id', $community->id)->first() ?? Unit::factory()->create(['community_id' => $community->id]);
$residentUser = User::factory()->create();
$community->users()->attach($residentUser, ['role' => 'resident']);
Resident::factory()->create([
    'community_id' => $community->id,
    'unit_id' => $unit->id,
    'user_id' => $residentUser->id,
]);

// Trigger an emergency manually
try {
    app()->instance(\App\Services\TenantContext::class, new class($community) {
        public function __construct(private $community) {}
        public function community() { return $this->community; }
        public function require() { return $this->community; }
    });

    $action = new TriggerEmergencyEventAction();
    $event = $action->execute($residentUser, $unit, ['type' => 'panic', 'description' => 'Manual test trigger']);
    
    echo "Successfully triggered Event ID: " . $event->id . " for Unit: " . $unit->number . " in Community: " . $community->name . "\n";
    
    // Check SecurityLog
    $log = \App\Models\SecurityLog::where('subject_id', $event->id)->where('action', 'emergency_triggered')->first();
    echo "Security Log Created: " . ($log ? 'Yes' : 'No') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

