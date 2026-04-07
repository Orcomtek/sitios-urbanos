<?php

namespace App\Actions\Security;

use App\Models\AccessInvitation;
use App\Models\SecurityLog;
use App\Models\User;
use Exception;

class RevokeAccessInvitationAction
{
    public function execute(User $actor, AccessInvitation $invitation, ?string $reason = null): void
    {
        if ($invitation->status === 'used') {
            throw new Exception('Cannot revoke an already used invitation.');
        }

        if ($invitation->status === 'revoked') {
            return;
        }

        $invitation->update([
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);

        SecurityLog::create([
            'community_id' => $invitation->community_id,
            'actor_id' => $actor->id,
            'action' => 'access_invitation_revoked',
            'subject_type' => AccessInvitation::class,
            'subject_id' => $invitation->id,
            'details' => [
                'reason' => $reason,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
