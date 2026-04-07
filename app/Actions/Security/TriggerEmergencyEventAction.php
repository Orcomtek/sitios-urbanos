<?php

namespace App\Actions\Security;

use App\Models\EmergencyEvent;
use App\Models\Unit;
use App\Models\User;
use App\Models\SecurityLog;
use Illuminate\Validation\ValidationException;

class TriggerEmergencyEventAction
{
    public function execute(User $user, Unit $unit, array $data): EmergencyEvent
    {
        $type = $data['type'] ?? 'panic';

        $exists = EmergencyEvent::where('unit_id', $unit->id)
            ->where('type', $type)
            ->where('status', 'active')
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'type' => "An active emergency event of type {$type} already exists for this unit.",
            ]);
        }

        $emergency = EmergencyEvent::create([
            'community_id' => $unit->community_id,
            'unit_id' => $unit->id,
            'triggered_by' => $user->id,
            'type' => $type,
            'status' => 'active',
            'description' => $data['description'] ?? null,
            'triggered_at' => now(),
        ]);

        SecurityLog::create([
            'community_id' => $unit->community_id,
            'actor_id' => $user->id,
            'action' => 'emergency_triggered',
            'subject_type' => EmergencyEvent::class,
            'subject_id' => $emergency->id,
            'details' => [
                'type' => $type,
                'unit_id' => $unit->id,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        \App\Events\Security\EmergencyEventCreated::dispatch($emergency);

        return $emergency;
    }
}
