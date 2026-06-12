<?php

namespace App\Http\Controllers\Tenant\Admin\Governance;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Ticket;
use App\Notifications\NewTicketReplyNotification;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(): Response
    {
        $tickets = Ticket::with(['resident', 'unit'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'description' => $ticket->description,
                    'type' => $ticket->type,
                    'status' => $ticket->status,
                    'is_anonymous' => $ticket->is_anonymous,
                    'created_at' => $ticket->created_at,
                    'resident' => $ticket->resident,
                    'unit' => $ticket->unit,
                    'has_unread_admin' => (bool) $ticket->has_unread_admin,
                ];
            });

        return Inertia::render('Tenant/Admin/Governance/Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function show(string $community_slug, Ticket $ticket): Response
    {
        $ticket->load(['resident.user', 'unit', 'replies.user']);

        $ticket->update(['has_unread_admin' => false]);

        $residentContext = null;
        if (! $ticket->is_anonymous && $ticket->resident && $ticket->unit) {
            $community = app(TenantContext::class)->require();
            $resident = $ticket->resident;
            $unit = $ticket->unit;

            $pivot = DB::table('community_user')
                ->where('community_id', $community->id)
                ->where('unit_id', $unit->id)
                ->where('user_id', $resident->user_id)
                ->first();

            if ($pivot) {
                $baseRole = $pivot->resident_role ?? $resident->resident_type->value;
                $role = 'unknown';
                $sponsorName = null;

                if (in_array($baseRole, ['owner', 'propietario'])) {
                    $role = 'owner';
                } elseif (in_array($baseRole, ['tenant', 'inquilino'])) {
                    $role = 'tenant';

                    $sponsorId = $pivot->invited_by_user_id ?? null;
                    if ($sponsorId) {
                        $sponsorResident = Resident::where('user_id', $sponsorId)
                            ->where('unit_id', $unit->id)
                            ->first();
                        if ($sponsorResident) {
                            $sponsorName = $sponsorResident->full_name;
                        }
                    }
                } elseif (in_array($baseRole, ['family', 'dependent', 'familiar'])) {
                    // Default to family_owner if we can't figure it out, but strictly mapping it:
                    $role = 'family_owner';

                    $sponsorId = $pivot->invited_by_user_id ?? null;
                    if ($sponsorId) {
                        $sponsorPivot = DB::table('community_user')
                            ->where('community_id', $community->id)
                            ->where('unit_id', $unit->id)
                            ->where('user_id', $sponsorId)
                            ->first();

                        $sponsorResident = Resident::where('user_id', $sponsorId)
                            ->where('unit_id', $unit->id)
                            ->first();

                        $sponsorRole = $sponsorPivot->resident_role ?? null;
                        if (! $sponsorRole) {
                            $sponsorRole = $sponsorResident ? $sponsorResident->resident_type->value : null;
                        }

                        if (in_array($sponsorRole, ['owner', 'propietario'])) {
                            $role = 'family_owner';
                        } elseif (in_array($sponsorRole, ['tenant', 'inquilino'])) {
                            $role = 'family_tenant';
                        }

                        if ($sponsorResident) {
                            $sponsorName = $sponsorResident->full_name;
                        }
                    }
                }

                $residentContext = [
                    'role' => $role,
                    'sponsor_name' => $sponsorName,
                    'is_active' => (bool) $resident->is_active,
                ];
            }
        }

        if ($ticket->is_anonymous) {
            if ($ticket->resident) {
                $ticket->resident->full_name = 'Residente Anónimo';
                $ticket->resident->email = null;
                $ticket->resident->phone = null;
            }
            if ($ticket->unit) {
                $ticket->unit->identifier = 'Unidad Protegida';
            }
        }

        return Inertia::render('Tenant/Admin/Governance/Tickets/Show', [
            'ticket' => $ticket,
            'residentContext' => $residentContext,
        ]);
    }

    public function updateStatus(Request $request, string $community_slug, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update(['status' => $validated['status']]);

        return back()->with('success', 'Estado del ticket actualizado exitosamente.');
    }

    public function reply(Request $request, string $community_slug, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        if (! in_array($ticket->status, ['open', 'in_progress'])) {
            abort(403, 'Ticket cerrado.');
        }

        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $ticket->update(['has_unread_resident' => true]);

        if ($ticket->resident && $ticket->resident->user) {
            $ticket->resident->user->notify(new NewTicketReplyNotification($ticket));
        }

        return back()->with('success', 'Respuesta enviada exitosamente.');
    }
}
