<?php

namespace App\Actions\Security;

use App\Models\AccessInvitation;
use App\Models\SecurityLog;
use App\Models\User;
use Exception;

class ConsumeAccessInvitationAction
{
    public function execute(User $actor, AccessInvitation $invitation): void
    {
        // First check if it's already expired physically but not updated.
        if ($invitation->status === 'active' && $invitation->expires_at->isPast()) {
            $invitation->update(['status' => 'expired']);

            SecurityLog::create([
                'community_id' => $invitation->community_id,
                'actor_id' => $actor->id,
                'action' => 'access_invitation_auto_expired',
                'subject_type' => AccessInvitation::class,
                'subject_id' => $invitation->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        if ($invitation->status !== 'active') {
            throw new Exception("This invitation cannot be consumed. It is currently {$invitation->status}.");
        }

        $invitation->update([
            'status' => 'used',
            'used_at' => now(),
        ]);

        SecurityLog::create([
            'community_id' => $invitation->community_id,
            'actor_id' => $actor->id,
            'action' => 'access_invitation_consumed',
            'subject_type' => AccessInvitation::class,
            'subject_id' => $invitation->id,
            'details' => [
                'unit_id' => $invitation->unit_id,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
