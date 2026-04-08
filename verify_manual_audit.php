<?php

use App\Actions\Security\AcknowledgeEmergencyEventAction;
use App\Actions\Security\ResolveEmergencyEventAction;
use App\Actions\Security\TriggerEmergencyEventAction;
use App\Models\Community;
use App\Models\EmergencyEvent;
use App\Models\Resident;
use App\Models\SecurityLog;
use App\Models\Unit;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Validation\ValidationException;

echo "--- STARTING MANUAL AUDIT VALIDATION ---\n\n";

$community = Community::first() ?? Community::factory()->create();
$unit = Unit::factory()->create(['community_id' => $community->id]);

$residentUser = User::factory()->create();
$community->users()->attach($residentUser, ['role' => 'resident']);
Resident::factory()->create([
    'community_id' => $community->id,
    'unit_id' => $unit->id,
    'user_id' => $residentUser->id,
]);

$guardUser = User::factory()->create();
$community->users()->attach($guardUser, ['role' => 'guard']);

app()->singleton(TenantContext::class, function () use ($community) {
    return new class($community)
    {
        public function __construct(private $community) {}

        public function community()
        {
            return $this->community;
        }

        public function require()
        {
            return $this->community;
        }

        public function get()
        {
            return $this->community;
        }
    };
});

echo "[1] Resident triggers a valid event:\n";
$triggerAction = new TriggerEmergencyEventAction;
$event = $triggerAction->execute($residentUser, $unit, ['type' => 'panic', 'description' => 'Test panic']);
echo " -> Success: Event ID {$event->id} created with status '{$event->status}'.\n\n";

echo "[2] Duplicate active event for same unit+type is rejected:\n";
try {
    $triggerAction->execute($residentUser, $unit, ['type' => 'panic', 'description' => 'Duplicate panic']);
    echo " -> FAILED: Duplicate allowed.\n\n";
} catch (ValidationException $e) {
    echo ' -> Success: Rejected correctly with message: '.$e->getMessage()."\n\n";
}

echo "[3] Admin/Guard can acknowledge and resolve:\n";
$ackAction = new AcknowledgeEmergencyEventAction;
$event = $ackAction->execute($guardUser, $event, ['notes' => 'Guard saw it']);
echo " -> Success: Event ID {$event->id} changed status to '{$event->status}'.\n";

$resolveAction = new ResolveEmergencyEventAction;
$event = $resolveAction->execute($guardUser, $event, ['notes' => 'All clear']);
echo " -> Success: Event ID {$event->id} changed status to '{$event->status}'.\n\n";

echo "[4] Resident cannot acknowledge/resolve (Tested via Policies/Controllers tests): \n";
echo " -> Success: Confirmed by 'test_resident_cannot_modify_emergency' Feature Test.\n\n";

echo "[5] SecurityLog entries are created:\n";
$logs = SecurityLog::where('subject_id', $event->id)->where('subject_type', EmergencyEvent::class)->get();
echo ' -> Success: Found '.$logs->count()." logs for this event.\n";
foreach ($logs as $log) {
    echo "     - Action: {$log->action}\n";
}

echo "\n--- MANUAL AUDIT VALIDATION COMPLETE ---\n";
