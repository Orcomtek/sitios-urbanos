<?php

namespace App\Actions\Security;

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Validation\ValidationException;

class TransitionVisitorStatusAction
{
    public function execute(Visitor $visitor, string $newStatus, User $user, Community $community): Visitor
    {
        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            throw ValidationException::withMessages([
                'status' => 'No tienes permisos para registrar la entrada o salida de visitantes.',
            ]);
        }

        if ($newStatus === 'entered') {
            if ($visitor->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'El visitante no está en estado pendiente.',
                ]);
            }
            
            $visitor->update([
                'status' => 'entered',
                'entered_at' => now(),
            ]);
        } elseif ($newStatus === 'exited') {
            if ($visitor->status !== 'entered') {
                throw ValidationException::withMessages([
                    'status' => 'El visitante no ha ingresado.',
                ]);
            }
            
            $visitor->update([
                'status' => 'exited',
                'exited_at' => now(),
            ]);
        } else {
            throw ValidationException::withMessages([
                'status' => 'Estado no válido.',
            ]);
        }

        return $visitor;
    }
}
