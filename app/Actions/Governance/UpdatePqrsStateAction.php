<?php

namespace App\Actions\Governance;

use App\Enums\PqrsStatus;
use App\Models\Pqrs;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdatePqrsStateAction
{
    public function execute(Pqrs $pqrs, PqrsStatus $newStatus, User $actor, ?string $adminResponse = null): Pqrs
    {
        return DB::transaction(function () use ($pqrs, $newStatus, $actor, $adminResponse) {
            $oldStatus = $pqrs->status;

            if ($adminResponse !== null) {
                $pqrs->admin_response = $adminResponse;

                // If it's the first time an admin responds, record the timestamp
                if ($pqrs->responded_at === null) {
                    $pqrs->responded_at = now();
                }
            }

            if ($newStatus !== $oldStatus) {
                $pqrs->status = $newStatus;

                if (in_array($newStatus, [PqrsStatus::CLOSED, PqrsStatus::REJECTED])) {
                    $pqrs->closed_at = now();
                }

                SecurityLog::create([
                    'community_id' => $pqrs->community_id,
                    'actor_id' => $actor->id,
                    'action' => 'pqrs.status_updated',
                    'subject_type' => Pqrs::class,
                    'subject_id' => $pqrs->id,
                    'details' => [
                        'old_status' => $oldStatus->value,
                        'new_status' => $newStatus->value,
                        'has_response' => $adminResponse !== null,
                    ],
                ]);
            }

            $pqrs->save();

            return $pqrs;
        });
    }
}
