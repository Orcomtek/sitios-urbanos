<?php

namespace App\Http\Controllers\Tenant\Resident;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index(string $communitySlug)
    {
        $resident = $this->getActiveResident();

        if (!$resident) {
            abort(403, 'No tienes un perfil de residente activo en esta comunidad.');
        }

        $tickets = Ticket::where('resident_id', $resident->id)->latest()->get();

        return Inertia::render('Tenant/Resident/Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function store(Request $request, string $communitySlug)
    {
        $resident = $this->getActiveResident();

        if (!$resident) {
            abort(403);
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:peticion,queja,reclamo,sugerencia',
            'is_anonymous' => 'boolean',
        ]);

        $validated['community_id'] = $resident->community_id;
        $validated['resident_id'] = $resident->id;
        $validated['unit_id'] = $resident->unit_id;
        $validated['status'] = 'open';

        Ticket::create($validated);

        return back()->with('success', 'Ticket creado correctamente.');
    }

    public function update(Request $request, string $communitySlug, Ticket $ticket)
    {
        $resident = $this->getActiveResident();

        if (!$resident || $ticket->resident_id !== $resident->id) {
            abort(403);
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:peticion,queja,reclamo,sugerencia',
            'is_anonymous' => 'boolean',
        ]);

        $ticket->update($validated);

        return back()->with('success', 'Ticket actualizado correctamente.');
    }

    public function destroy(string $communitySlug, Ticket $ticket)
    {
        $resident = $this->getActiveResident();

        if (!$resident || $ticket->resident_id !== $resident->id) {
            abort(403);
        }

        $ticket->delete();

        return back()->with('success', 'Ticket eliminado correctamente.');
    }

    private function getActiveResident()
    {
        return Resident::where('user_id', Auth::id())->active()->first();
    }
}
