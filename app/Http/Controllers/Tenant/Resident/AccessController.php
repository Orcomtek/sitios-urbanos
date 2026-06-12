<?php

namespace App\Http\Controllers\Tenant\Resident;

use App\Actions\CoreOperations\RevokeAccessAction;
use App\Http\Controllers\Controller;
use App\Mail\CommunityInvitationMail;
use App\Models\Resident;
use App\Models\User;
use App\Models\UserInvitation;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AccessController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request, string $community_slug)
    {
        $community = $this->context->require();
        $user = $request->user();

        $resident = Resident::with('unit')
            ->where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->active()
            ->firstOrFail();

        $currentUserPivot = DB::table('community_user')
            ->where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('unit_id', $resident->unit_id)
            ->first();

        $currentResidentRole = $currentUserPivot->resident_role ?? $resident->resident_type->value;

        if (! $currentResidentRole || ! in_array($currentResidentRole, ['owner', 'propietario', 'tenant', 'inquilino'])) {
            abort(403, 'No tienes privilegios para patrocinar accesos.');
        }

        $sponsoredUsers = DB::table('community_user')
            ->join('users', 'community_user.user_id', '=', 'users.id')
            ->where('community_user.community_id', $community->id)
            ->where('community_user.unit_id', $resident->unit_id)
            ->where('community_user.invited_by_user_id', $user->id)
            ->select('users.id', 'users.name', 'users.email', 'community_user.resident_role', 'community_user.created_at')
            ->get();

        $pendingInvitations = UserInvitation::where('community_id', $community->id)
            ->where('unit_id', $resident->unit_id)
            ->where('invited_by', $user->id)
            ->where('status', 'pending')
            ->get(['id', 'email', 'resident_role', 'created_at', 'status']);

        foreach ($sponsoredUsers as $sponsored) {
            $sponsored->computed_role = $sponsored->resident_role;
            if ($sponsored->resident_role === 'family') {
                $sponsored->computed_role = in_array($currentResidentRole, ['owner', 'propietario']) ? 'family_owner' : 'family_tenant';
            }
        }

        foreach ($pendingInvitations as $invitation) {
            $invitation->computed_role = $invitation->resident_role;
            if ($invitation->resident_role === 'family') {
                $invitation->computed_role = in_array($currentResidentRole, ['owner', 'propietario']) ? 'family_owner' : 'family_tenant';
            }
        }

        $activeTenant = Resident::where('community_id', $community->id)
            ->where('unit_id', $resident->unit_id)
            ->where('is_active', true)
            ->whereIn('resident_type', ['tenant', 'inquilino'])
            ->first();

        return Inertia::render('Tenant/Resident/Access/Index', [
            'sponsoredUsers' => $sponsoredUsers,
            'pendingInvitations' => $pendingInvitations,
            'residentRole' => $resident->resident_type->value,
            'currentResidentRole' => $currentResidentRole,
            'unit' => $resident->unit->identifier,
            'activeUnitTenant' => $activeTenant ? ['id' => $activeTenant->user_id, 'name' => $activeTenant->full_name] : null,
        ]);
    }

    public function store(Request $request, string $community_slug, RevokeAccessAction $revokeAction)
    {
        $community = $this->context->require();
        $user = $request->user();

        $resident = Resident::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->active()
            ->firstOrFail();

        $currentUserPivot = DB::table('community_user')
            ->where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('unit_id', $resident->unit_id)
            ->first();

        $currentResidentRole = $currentUserPivot->resident_role ?? $resident->resident_type->value;

        if (! $currentResidentRole || ! in_array($currentResidentRole, ['owner', 'propietario', 'tenant', 'inquilino'])) {
            abort(403, 'No tienes privilegios para patrocinar accesos.');
        }

        $allowedRoles = ['family'];
        if (in_array($currentResidentRole, ['owner', 'propietario'])) {
            $allowedRoles[] = 'tenant';
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in($allowedRoles)],
            'replace_existing' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($community, $user, $resident, $validated, $revokeAction) {
            if (! empty($validated['replace_existing']) && $validated['replace_existing']) {
                $legacyTenant = Resident::where('community_id', $community->id)
                    ->where('unit_id', $resident->unit_id)
                    ->where('is_active', true)
                    ->whereIn('resident_type', ['tenant', 'inquilino'])
                    ->first();

                if ($legacyTenant && $legacyTenant->user_id) {
                    $activeTenantUser = User::find($legacyTenant->user_id);
                    if ($activeTenantUser) {
                        $revokeAction->execute($community, $resident->unit_id, $activeTenantUser, $user);
                    }
                }
            }

            $invitation = UserInvitation::updateOrCreate(
                [
                    'email' => $validated['email'],
                    'community_id' => $community->id,
                ],
                [
                    'name' => $validated['name'],
                    'token' => UserInvitation::generateSecureToken(),
                    'role' => 'resident',
                    'resident_role' => $validated['role'],
                    'unit_id' => $resident->unit_id,
                    'invited_by' => $user->id,
                    'expires_at' => now()->addDays(7),
                    'status' => 'pending',
                ]
            );

            Mail::to($validated['email'])
                ->queue(new CommunityInvitationMail($invitation));
        });

        return redirect()->back()->with('success', 'Invitación enviada exitosamente.');
    }

    public function revoke(Request $request, string $community_slug, User $sponsoredUser, RevokeAccessAction $action)
    {
        $community = $this->context->require();
        $user = $request->user();

        $resident = Resident::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->active()
            ->firstOrFail();

        $currentUserPivot = DB::table('community_user')
            ->where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('unit_id', $resident->unit_id)
            ->first();

        $currentResidentRole = $currentUserPivot->resident_role ?? $resident->resident_type->value;

        if (! $currentResidentRole || ! in_array($currentResidentRole, ['owner', 'propietario', 'tenant', 'inquilino'])) {
            abort(403, 'No tienes privilegios para patrocinar accesos.');
        }

        $isSponsored = DB::table('community_user')
            ->where('community_id', $community->id)
            ->where('unit_id', $resident->unit_id)
            ->where('user_id', $sponsoredUser->id)
            ->where('invited_by_user_id', $user->id)
            ->exists();

        if (! $isSponsored) {
            abort(403, 'No tienes permiso para revocar este acceso.');
        }

        $action->execute($community, $resident->unit_id, $sponsoredUser, $user);

        return redirect()->back()->with('success', 'Acceso revocado exitosamente.');
    }
}
