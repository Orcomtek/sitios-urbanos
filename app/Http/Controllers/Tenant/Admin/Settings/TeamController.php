<?php

namespace App\Http\Controllers\Tenant\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvitation;
use App\Mail\CommunityInvitationMail;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(string $community_slug)
    {
        $tenant = $this->context->require();

        $staffRoles = ['tenant_admin', 'sub_admin', 'accountant', 'auditor', 'guard'];

        $team = User::whereHas('communities', function ($q) use ($tenant, $staffRoles) {
            $q->where('community_id', $tenant->id)
              ->whereIn('role', $staffRoles);
        })
        ->with(['communities' => function ($q) use ($tenant) {
            $q->where('community_id', $tenant->id);
        }])
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->communities->first()->pivot->role ?? 'unknown',
            ];
        });

        // Also get pending staff invitations
        $pendingInvitations = UserInvitation::where('community_id', $tenant->id)
            ->whereIn('role', $staffRoles)
            ->where('status', 'pending')
            ->get();

        return Inertia::render('Tenant/Admin/Settings/Team/Index', [
            'team' => $team,
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function invite(Request $request, string $community_slug)
    {
        $tenant = $this->context->require();
        $staffRoles = ['tenant_admin', 'sub_admin', 'accountant', 'auditor', 'guard'];

        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:' . implode(',', $staffRoles),
        ]);

        $token = Str::random(64);

        $invitation = UserInvitation::create([
            'community_id' => $tenant->id,
            'email' => $validated['email'],
            'name' => $validated['name'],
            'role' => $validated['role'],
            'token' => $token,
            'invited_by' => $request->user()->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($validated['email'])->send(new CommunityInvitationMail($invitation));

        return back()->with('success', 'Invitación enviada exitosamente.');
    }

    public function update(Request $request, string $community_slug, User $user)
    {
        $tenant = $this->context->require();
        $staffRoles = ['tenant_admin', 'sub_admin', 'accountant', 'auditor', 'guard'];

        $validated = $request->validate([
            'role' => 'required|string|in:' . implode(',', $staffRoles),
        ]);

        $membership = $user->communities()->where('community_id', $tenant->id)->first();

        if (!$membership) {
            abort(404, 'Usuario no encontrado en esta comunidad.');
        }

        if ($membership->pivot->role === 'tenant_admin' && $validated['role'] !== 'tenant_admin') {
            $adminCount = User::whereHas('communities', function ($q) use ($tenant) {
                $q->where('community_id', $tenant->id)->where('role', 'tenant_admin');
            })->count();

            if ($adminCount <= 1) {
                return back()->withErrors(['role' => 'No puedes cambiar el rol del último administrador principal.']);
            }
        }

        $user->communities()->updateExistingPivot($tenant->id, [
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Rol de usuario actualizado exitosamente.');
    }

    public function destroy(Request $request, string $community_slug, User $user)
    {
        $tenant = $this->context->require();

        $membership = $user->communities()->where('community_id', $tenant->id)->first();
        
        if (!$membership) {
            abort(404, 'Usuario no encontrado en esta comunidad.');
        }

        if ($membership->pivot->role === 'tenant_admin') {
            $adminCount = User::whereHas('communities', function ($q) use ($tenant) {
                $q->where('community_id', $tenant->id)->where('role', 'tenant_admin');
            })->count();

            if ($adminCount <= 1) {
                return back()->withErrors(['role' => 'No puedes revocar al último administrador principal.']);
            }
        }

        $user->communities()->detach($tenant->id);

        return back()->with('success', 'Acceso revocado exitosamente.');
    }
}
