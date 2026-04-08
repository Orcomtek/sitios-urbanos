<?php

namespace App\Actions\Security;

use App\Models\EmergencyEvent;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ResolveEmergencyEventAction
{
    public function execute(User $user, EmergencyEvent $emergency, array $data = []): EmergencyEvent
    {
        if ($emergency->status === 'resolved') {
            throw ValidationException::withMessages([
                'status' => 'Event is already resolved.',
            ]);
        }

        $notes = $data['notes'] ?? $emergency->notes;

        $emergency->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'notes' => $notes,
        ]);

        SecurityLog::create([
            'community_id' => $emergency->community_id,
            'actor_id' => $user->id,
            'action' => 'emergency_resolved',
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
