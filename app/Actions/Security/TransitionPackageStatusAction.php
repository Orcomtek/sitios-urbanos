<?php

namespace App\Actions\Security;

use App\Enums\CommunityRole;
use App\Models\Community;
use App\Models\Package;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TransitionPackageStatusAction
{
    public function execute(Package $package, string $newStatus, User $user, Community $community, ?string $notes = null): Package
    {
        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            throw ValidationException::withMessages([
                'status' => 'No tienes permisos para registrar la entrega o devolución de paquetes.',
            ]);
        }

        if ($newStatus === 'delivered') {
            if ($package->status !== 'received') {
                throw ValidationException::withMessages([
                    'status' => 'El paquete no está en estado recibido o ya fue procesado.',
                ]);
            }

            $package->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivered_by' => $user->id,
                'notes' => $notes ?? $package->notes,
            ]);
        } elseif ($newStatus === 'returned') {
            if ($package->status !== 'received') {
                throw ValidationException::withMessages([
                    'status' => 'El paquete no está en estado recibido o ya fue procesado.',
                ]);
            }

            $package->update([
                'status' => 'returned',
                'returned_at' => now(),
                // Using delivered_by to record who processed the return on the staff side (the operational anchor as per rules)
                'delivered_by' => $user->id,
                'notes' => $notes ?? $package->notes,
            ]);
        } else {
            throw ValidationException::withMessages([
                'status' => 'Estado no válido.',
            ]);
        }

        return $package;
    }
}
