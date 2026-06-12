<?php

namespace App\Actions\CoreOperations;

use App\Actions\Security\LogSecurityEventAction;
use App\Models\Community;
use App\Models\Resident;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RevokeAccessAction
{
    public function __construct(
        private LogSecurityEventAction $logSecurityEventAction
    ) {}

    public function execute(Community $community, int $unitId, User $targetUser, ?User $actor = null): void
    {
        DB::transaction(function () use ($community, $unitId, $targetUser, $actor) {
            $this->internalExecute($community, $unitId, $targetUser, $actor);
        });
    }

    private function internalExecute(Community $community, int $unitId, User $targetUser, ?User $actor = null): void
    {
        // 1. Mark the corresponding Resident model as inactive
        $resident = Resident::where('community_id', $community->id)
            ->where('unit_id', $unitId)
            ->where('user_id', $targetUser->id)
            ->first();

        if ($resident) {
            $resident->update(['is_active' => false]);

            // Auto-close open tickets for this resident
            $openTickets = Ticket::where('resident_id', $resident->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->get();

            foreach ($openTickets as $ticket) {
                $ticket->update(['status' => 'closed']);

                TicketReply::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id() ?? $actor?->id,
                    'message' => '🤖 Sistema: Ticket cerrado automáticamente debido a la revocación de acceso del residente a la copropiedad.',
                ]);
            }
        }

        // 2. Detach from community_user pivot for this specific community
        $community->users()->detach($targetUser->id);

        $this->logSecurityEventAction->execute(
            $community,
            $actor ?? $targetUser,
            'resident.access_revoked',
            $targetUser,
            ['reason' => 'Access revoked by sponsor or admin', 'unit_id' => $unitId]
        );

        // 3. Cascading Revocation
        // Find all users invited by this user in this community/unit
        $sponsoredUserIds = DB::table('community_user')
            ->where('community_id', $community->id)
            ->where('unit_id', $unitId)
            ->where('invited_by_user_id', $targetUser->id)
            ->pluck('user_id');

        foreach ($sponsoredUserIds as $sponsoredUserId) {
            $sponsoredUser = User::find($sponsoredUserId);
            if ($sponsoredUser) {
                // Recursively revoke their access
                $this->internalExecute($community, $unitId, $sponsoredUser, $actor);
            }
        }
    }
}
