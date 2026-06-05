<?php

namespace App\Http\Controllers\Tenant\Resident;

use App\Http\Controllers\Controller;
use App\Models\MoveRequest;
use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MoveRequestController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext)
    {
        $community = $tenantContext->get();
        $user = $request->user();

        $residents = Resident::with('unit')
            ->where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->get();

        $residentIds = $residents->pluck('id');

        $moveRequests = MoveRequest::with(['unit'])
            ->where('community_id', $community->id)
            ->whereIn('resident_id', $residentIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Tenant/Resident/Logistics/Moves/Index', [
            'moveRequests' => $moveRequests,
            'residents' => $residents,
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext)
    {
        $community = $tenantContext->get();

        $validated = $request->validate([
            'resident_id' => ['required', 'exists:residents,id'],
            'type' => ['required', 'in:move_in,move_out,internal_transfer'],
            'requested_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'scale' => ['required', 'in:light,medium,heavy'],
        ]);

        $resident = Resident::where('id', $validated['resident_id'])
            ->where('community_id', $community->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        MoveRequest::create([
            'community_id' => $community->id,
            'resident_id' => $resident->id,
            'unit_id' => $resident->unit_id,
            'type' => $validated['type'],
            'requested_date' => $validated['requested_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'scale' => $validated['scale'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Solicitud de autorización enviada correctamente.');
    }
}
