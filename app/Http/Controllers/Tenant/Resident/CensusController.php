<?php

namespace App\Http\Controllers\Tenant\Resident;

use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use App\Models\Pet;
use App\Models\Resident;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CensusController extends Controller
{
    public function index(string $communitySlug)
    {
        $resident = $this->getActiveResident();

        if (!$resident) {
            abort(403, 'No tienes un perfil de residente activo en esta comunidad.');
        }

        $householdResidentIds = $this->getHouseholdResidentIds($resident);

        $familyMembers = FamilyMember::whereIn('resident_id', $householdResidentIds)->get();
        $vehicles = Vehicle::whereIn('resident_id', $householdResidentIds)->get();
        $pets = Pet::whereIn('resident_id', $householdResidentIds)->get();

        return Inertia::render('Tenant/Resident/Census/Index', [
            'familyMembers' => $familyMembers,
            'vehicles' => $vehicles,
            'pets' => $pets,
        ]);
    }

    public function storeFamilyMember(Request $request, string $communitySlug)
    {
        $resident = $this->getActiveResident();

        if (!$resident) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|in:spouse,child,parent,other',
            'is_minor' => 'boolean',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $validated['community_id'] = $resident->community_id;
        $validated['resident_id'] = $resident->id;

        FamilyMember::create($validated);

        return back()->with('success', 'Familiar registrado correctamente.');
    }

    public function updateFamilyMember(Request $request, string $communitySlug, FamilyMember $familyMember)
    {
        $resident = $this->getActiveResident();

        if (!$resident || !in_array($familyMember->resident_id, $this->getHouseholdResidentIds($resident))) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|in:spouse,child,parent,other',
            'is_minor' => 'boolean',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $familyMember->update($validated);

        return back()->with('success', 'Familiar actualizado correctamente.');
    }

    public function destroyFamilyMember(string $communitySlug, FamilyMember $familyMember)
    {
        $resident = $this->getActiveResident();

        if (!$resident || !in_array($familyMember->resident_id, $this->getHouseholdResidentIds($resident))) {
            abort(403);
        }

        $familyMember->delete();

        return back()->with('success', 'Familiar eliminado correctamente.');
    }

    public function storeVehicle(Request $request, string $communitySlug)
    {
        $resident = $this->getActiveResident();

        if (!$resident) {
            abort(403);
        }

        $validated = $request->validate([
            'license_plate' => 'required|string|max:20',
            'brand' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'type' => 'required|in:car,motorcycle,bicycle,other',
        ]);

        $validated['community_id'] = $resident->community_id;
        $validated['resident_id'] = $resident->id;

        Vehicle::create($validated);

        return back()->with('success', 'Vehículo registrado correctamente.');
    }

    public function updateVehicle(Request $request, string $communitySlug, Vehicle $vehicle)
    {
        $resident = $this->getActiveResident();

        if (!$resident || !in_array($vehicle->resident_id, $this->getHouseholdResidentIds($resident))) {
            abort(403);
        }

        $validated = $request->validate([
            'license_plate' => 'required|string|max:20',
            'brand' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'type' => 'required|in:car,motorcycle,bicycle,other',
        ]);

        $vehicle->update($validated);

        return back()->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroyVehicle(string $communitySlug, Vehicle $vehicle)
    {
        $resident = $this->getActiveResident();

        if (!$resident || !in_array($vehicle->resident_id, $this->getHouseholdResidentIds($resident))) {
            abort(403);
        }

        $vehicle->delete();

        return back()->with('success', 'Vehículo eliminado correctamente.');
    }

    public function storePet(Request $request, string $communitySlug)
    {
        $resident = $this->getActiveResident();

        if (!$resident) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|in:dog,cat,other',
            'breed' => 'nullable|string|max:255',
        ]);

        $validated['community_id'] = $resident->community_id;
        $validated['resident_id'] = $resident->id;

        Pet::create($validated);

        return back()->with('success', 'Mascota registrada correctamente.');
    }

    public function updatePet(Request $request, string $communitySlug, Pet $pet)
    {
        $resident = $this->getActiveResident();

        if (!$resident || !in_array($pet->resident_id, $this->getHouseholdResidentIds($resident))) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|in:dog,cat,other',
            'breed' => 'nullable|string|max:255',
        ]);

        $pet->update($validated);

        return back()->with('success', 'Mascota actualizada correctamente.');
    }

    public function destroyPet(string $communitySlug, Pet $pet)
    {
        $resident = $this->getActiveResident();

        if (!$resident || !in_array($pet->resident_id, $this->getHouseholdResidentIds($resident))) {
            abort(403);
        }

        $pet->delete();

        return back()->with('success', 'Mascota eliminada correctamente.');
    }

    private function getActiveResident()
    {
        $community = app(\App\Services\TenantContext::class)->get();
        return Resident::where('user_id', Auth::id())
            ->where('community_id', $community->id)
            ->active()
            ->first();
    }

    private function getHouseholdResidentIds(Resident $resident)
    {
        $community = app(\App\Services\TenantContext::class)->get();
        $user = Auth::user();

        $pivot = \Illuminate\Support\Facades\DB::table('community_user')
            ->where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('unit_id', $resident->unit_id)
            ->first();

        $role = $pivot->resident_role ?? $resident->resident_type->value;
        $householdUserIds = [$user->id];

        if (in_array($role, ['owner', 'propietario', 'tenant', 'inquilino'])) {
            $sponsoredIds = \Illuminate\Support\Facades\DB::table('community_user')
                ->where('community_id', $community->id)
                ->where('unit_id', $resident->unit_id)
                ->where('invited_by_user_id', $user->id)
                ->pluck('user_id')
                ->toArray();
            
            $householdUserIds = array_merge($householdUserIds, $sponsoredIds);
        } else {
            $sponsorId = $pivot->invited_by_user_id ?? null;
            if ($sponsorId) {
                $sponsoredIds = \Illuminate\Support\Facades\DB::table('community_user')
                    ->where('community_id', $community->id)
                    ->where('unit_id', $resident->unit_id)
                    ->where('invited_by_user_id', $sponsorId)
                    ->pluck('user_id')
                    ->toArray();
                
                $householdUserIds = array_merge([$sponsorId], $sponsoredIds);
            }
        }

        return Resident::where('community_id', $community->id)
            ->where('unit_id', $resident->unit_id)
            ->whereIn('user_id', $householdUserIds)
            ->pluck('id')
            ->toArray();
    }
}
