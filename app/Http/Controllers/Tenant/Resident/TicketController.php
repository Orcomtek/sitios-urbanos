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

        $tickets = Ticket::where('resident_id', $resident->id)
            ->latest()
            ->get()
            ->map(function ($ticket) {
                $item = $ticket->toArray();
                $item['has_unread_admin'] = (bool) $ticket->has_unread_admin;
                $item['has_unread_resident'] = (bool) $ticket->has_unread_resident;
                return $item;
            });

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
        $validated['has_unread_admin'] = true;

        $ticket = Ticket::create($validated);

        if ($ticket->community) {
            $admins = $ticket->community->users()->wherePivotIn('role', ['tenant_admin', 'sub_admin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\NewTicketReplyNotification($ticket));
            }
        }

        return back()->with('success', 'Ticket creado correctamente.');
    }

    public function update(Request $request, string $communitySlug, Ticket $ticket)
    {
        $resident = $this->getActiveResident();

        if (!$resident || $ticket->resident_id !== $resident->id) {
            abort(403);
        }

        if ($ticket->status !== 'open') {
            return back()->withErrors(['error' => 'No puedes modificar un ticket que ya no está abierto.']);
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

        if ($ticket->status !== 'open') {
            return back()->withErrors(['error' => 'No puedes eliminar un ticket que ya no está abierto.']);
        }

        $ticket->delete();

        return back()->with('success', 'Ticket eliminado correctamente.');
    }

    public function show(string $communitySlug, Ticket $ticket)
    {
        $resident = $this->getActiveResident();

        if (!$resident || $ticket->resident_id !== $resident->id) {
            abort(403);
        }

        $ticket->load(['replies.user']);

        $ticket->update(['has_unread_resident' => false]);

        return Inertia::render('Tenant/Resident/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, string $communitySlug, Ticket $ticket)
    {
        $resident = $this->getActiveResident();

        if (!$resident || $ticket->resident_id !== $resident->id) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $ticket->update(['has_unread_admin' => true]);

        if ($ticket->community) {
            $admins = $ticket->community->users()->wherePivotIn('role', ['tenant_admin', 'sub_admin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\NewTicketReplyNotification($ticket));
            }
        }

        return back()->with('success', 'Respuesta enviada exitosamente.');
    }

    private function getActiveResident()
    {
        return Resident::where('user_id', Auth::id())->active()->first();
    }
}
