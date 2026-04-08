<?php

namespace App\Actions\Security;

use App\Models\EmergencyEvent;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AcknowledgeEmergencyEventAction
{
    public function execute(User $user, EmergencyEvent $emergency, array $data = []): EmergencyEvent
    {
        if ($emergency->status !== 'active') {
            throw ValidationException::withMessages([
                'status' => "Only active emergencies can be acknowledged. Current status: {$emergency->status}.",
            ]);
        }

        $emergency->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'notes' => $data['notes'] ?? $emergency->notes,
        ]);

        SecurityLog::create([
            'community_id' => $emergency->community_id,
            'actor_id' => $user->id,
            'action' => 'emergency_acknowledged',
            'subject_type' => EmergencyEvent::class,
            'subject_id' => $emergency->id,
            'details' => [
                'notes' => $data['notes'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $emergency;
    }
}
