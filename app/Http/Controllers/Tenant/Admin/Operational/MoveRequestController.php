<?php

namespace App\Http\Controllers\Tenant\Admin\Operational;

use App\Http\Controllers\Controller;
use App\Models\MoveRequest;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Notifications\Tenant\Resident\MoveRequestResolvedNotification;

class MoveRequestController extends Controller
{
    public function index(TenantContext $tenantContext)
    {
        $moveRequests = MoveRequest::with(['resident.user', 'unit'])
            ->where('community_id', $tenantContext->get()->id)
            ->orderBy('requested_date', 'asc')
            ->get();

        return Inertia::render('Tenant/Admin/Operational/Moves/Index', [
            'moveRequests' => $moveRequests,
        ]);
    }

    public function update(Request $request, string $community_slug, MoveRequest $moveRequest, TenantContext $tenantContext)
    {
        if ($moveRequest->community_id !== $tenantContext->get()->id) {
            abort(403);
        }

        $rules = [
            'status' => 'required|in:approved,denied,modified',
            'admin_notes' => 'nullable|string',
            'requested_date' => 'nullable|date',
        ];

        if ($request->status === 'modified') {
            $rules['requested_date'] = 'required|date';
            $rules['start_time'] = 'required|string';
            $rules['end_time'] = 'required|string';
        }

        $validated = $request->validate($rules);

        $updateData = [
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'resolved_by' => auth()->id(),
        ];

        $moveRequest->update($updateData);

        if ($request->status === 'modified') {
            $moveRequest->requested_date = $request->requested_date;
            $moveRequest->start_time = $request->start_time;
            $moveRequest->end_time = $request->end_time;
            $moveRequest->save();
        }

        $moveRequest->loadMissing(['resident.user', 'unit', 'community']);
        if ($moveRequest->resident && $moveRequest->resident->user) {
            $moveRequest->resident->user->notify(new MoveRequestResolvedNotification($moveRequest));
        }

        return redirect()->back()->with('success', 'Solicitud actualizada correctamente.');
    }
}
