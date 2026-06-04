<?php

namespace App\Http\Controllers\Tenant\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use App\Models\Pet;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RadarController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tenant/Admin/Security/Radar/Index');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        if (empty($query)) {
            return response()->json([
                'units' => [],
                'vehicles' => [],
                'pets' => [],
                'family_members' => [],
                'residents' => [],
            ]);
        }

        $units = Unit::with(['residents' => function($q) {
            $q->where('is_active', true);
        }, 'residents.familyMembers'])
            ->where('identifier', 'ilike', '%' . $query . '%')
            ->limit(10)
            ->get();

        $vehicles = Vehicle::with('resident.unit')
            ->whereHas('resident', function($q) {
                $q->where('is_active', true);
            })
            ->where('license_plate', 'ilike', '%' . $query . '%')
            ->limit(10)
            ->get();

        $pets = Pet::with('resident.unit')
            ->whereHas('resident', function($q) {
                $q->where('is_active', true);
            })
            ->where('name', 'ilike', '%' . $query . '%')
            ->limit(10)
            ->get();

        $familyMembers = FamilyMember::with('resident.unit')
            ->whereHas('resident', function($q) {
                $q->where('is_active', true);
            })
            ->where('name', 'ilike', '%' . $query . '%')
            ->limit(10)
            ->get();

        $residents = Resident::with('unit')
            ->where('is_active', true)
            ->where('full_name', 'ilike', '%' . $query . '%')
            ->limit(10)
            ->get();

        if ($residents->isNotEmpty()) {
            $community = app(\App\Services\TenantContext::class)->require();
            $sponsorPivots = \Illuminate\Support\Facades\DB::table('community_user')
                ->where('community_id', $community->id)
                ->whereIn('user_id', $residents->pluck('user_id'))
                ->get()
                ->keyBy(function ($p) { return $p->user_id . '_' . $p->unit_id; });

            $sponsorIds = $sponsorPivots->pluck('invited_by_user_id')->filter()->unique();
            $sponsorPivotsMap = [];
            if ($sponsorIds->isNotEmpty()) {
                $sponsorPivotsMap = \Illuminate\Support\Facades\DB::table('community_user')
                    ->where('community_id', $community->id)
                    ->whereIn('user_id', $sponsorIds)
                    ->get()
                    ->keyBy(function ($p) { return $p->user_id . '_' . $p->unit_id; });
            }

            foreach ($residents as $resident) {
                $key = $resident->user_id . '_' . $resident->unit_id;
                $pivot = $sponsorPivots->get($key);
                $role = $pivot->resident_role ?? $resident->resident_type->value;
                $resident->computed_role = $role;

                if (in_array($role, ['family', 'dependent'])) {
                    $sponsorId = $pivot->invited_by_user_id ?? null;
                    if ($sponsorId) {
                        $sponsorKey = $sponsorId . '_' . $resident->unit_id;
                        $sponsorPivot = $sponsorPivotsMap->get($sponsorKey) ?? null;
                        $sponsorRole = $sponsorPivot->resident_role ?? null;
                        
                        if (!$sponsorRole) {
                            $sponsorResident = Resident::where('user_id', $sponsorId)->where('unit_id', $resident->unit_id)->first();
                            $sponsorRole = $sponsorResident ? $sponsorResident->resident_type->value : null;
                        }

                        if (in_array($sponsorRole, ['owner', 'propietario'])) {
                            $resident->computed_role = 'family_owner';
                        } elseif (in_array($sponsorRole, ['tenant', 'inquilino'])) {
                            $resident->computed_role = 'family_tenant';
                        }
                    }
                }
            }
        }

        return response()->json([
            'units' => $units,
            'vehicles' => $vehicles,
            'pets' => $pets,
            'family_members' => $familyMembers,
            'residents' => $residents,
        ]);
    }
}
