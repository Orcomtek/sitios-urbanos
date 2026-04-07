<?php

namespace App\Actions\Security;

use App\Models\AccessInvitation;
use App\Models\Community;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Support\Str;

class CreateAccessInvitationAction
{
    public function execute(User $actor, Community $community, array $data): AccessInvitation
    {
        $code = strtoupper(Str::random(12)); // A 12-char secure non-sequential string.

        $expiresAt = $data['expires_at'] ?? now()->endOfDay();

        $invitation = AccessInvitation::create([
            'community_id' => $community->id,
            'unit_id' => $data['unit_id'],
            'visitor_id' => $data['visitor_id'] ?? null,
            'created_by' => $actor->id,
            'code' => $code,
            'type' => $data['type'] ?? 'manual_code',
            'status' => 'active',
            'expires_at' => $expiresAt,
            'notes' => $data['notes'] ?? null,
        ]);

        SecurityLog::create([
            'community_id' => $community->id,
            'actor_id' => $actor->id,
            'action' => 'access_invitation_created',
            'subject_type' => AccessInvitation::class,
            'subject_id' => $invitation->id,
            'details' => [
                'unit_id' => $invitation->unit_id,
                'type' => $invitation->type,
                'expires_at' => $invitation->expires_at->toIso8601String(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $invitation;
    }
}
