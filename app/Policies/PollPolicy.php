<?php

namespace App\Policies;

use App\Models\Governance\Poll;
use App\Models\User;
use App\Models\Resident;
use App\Services\TenantContext;

class PollPolicy
{
    /**
     * Determine whether the resident can vote on the poll.
     */
    public function vote(User $user, Poll $poll): bool
    {
        $tenantId = app(TenantContext::class)->get()->id;

        if ($poll->community_id !== $tenantId) {
            return false;
        }

        // Must be an active resident in this community
        $resident = Resident::where('community_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $resident) {
            return false;
        }

        // Cannot have already voted (Mutable state is strictly locked)
        $alreadyVoted = $poll->votes()
            ->where('community_id', $tenantId)
            ->where('user_id', $user->id)
            ->exists();

        return ! $alreadyVoted;
    }
}
