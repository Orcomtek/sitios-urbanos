<?php

namespace App\Services;

use App\Enums\ResidentType;
use App\Models\Resident;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ResidentOnboardingService
{
    /**
     * Onboards a new resident to a unit, enforcing business rules.
     */
    public function onboard(Unit $unit, array $data): Resident
    {
        // Explicitly inject the community_id into the payload
        $data['community_id'] = $unit->community_id;

        return DB::transaction(function () use ($unit, $data) {
            $type = ResidentType::from($data['resident_type']);

            if ($type === ResidentType::TENANT) {
                $this->deactivateCurrentTenantAndDependents($unit);
            }

            return $unit->residents()->create([
                'user_id' => $data['user_id'] ?? null,
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'resident_type' => $type,
                'is_active' => true,
                'pays_administration' => $data['pays_administration'] ?? false,
                'community_id' => $data['community_id'],
            ]);
        });
    }

    /**
     * Updates an existing resident, enforcing business rules.
     */
    public function update(Resident $resident, array $data): Resident
    {
        return DB::transaction(function () use ($resident, $data) {
            if (isset($data['resident_type'])) {
                $newType = ResidentType::from($data['resident_type']);
                
                // If changing to TENANT from something else, and becoming active
                $isActive = $data['is_active'] ?? $resident->is_active;
                
                if ($newType === ResidentType::TENANT && $isActive && ($resident->resident_type !== ResidentType::TENANT || !$resident->is_active)) {
                    $this->deactivateCurrentTenantAndDependents($resident->unit);
                }
            }

            // Ensure community_id is not overwritten by mistake
            unset($data['community_id'], $data['unit_id']);
            
            $resident->update($data);

            return $resident;
        });
    }

    /**
     * Deactivates the current active tenant and all active dependents in the unit.
     */
    private function deactivateCurrentTenantAndDependents(Unit $unit): void
    {
        // Find the currently active tenant
        $activeTenant = $unit->residents()
            ->active()
            ->tenants()
            ->first();

        if ($activeTenant) {
            $activeTenant->update(['is_active' => false]);
            
            // Deactivate all dependents in the unit as they are tied to the tenant
            $unit->residents()
                ->active()
                ->dependents()
                ->update(['is_active' => false]);
        }
    }
}
