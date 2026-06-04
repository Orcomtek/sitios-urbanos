<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Enums\ResidentType;

class AcceptInvitationController extends Controller
{
    /**
     * Show the acceptance form.
     */
    public function show(Request $request)
    {
        $token = $request->query('token');

        if (! $token) {
            abort(404, 'Token de invitación no proporcionado.');
        }

        $invitation = UserInvitation::where('token', $token)->with('community')->first();

        if (! $invitation || $invitation->status !== 'pending' || $invitation->expires_at->isPast()) {
            abort(403, 'La invitación es inválida o ha expirado.');
        }

        $userExists = User::where('email', $invitation->email)->exists();
        $isLoggedIn = Auth::check();

        $resident = Resident::where('community_id', $invitation->community_id)
            ->where('email', $invitation->email)
            ->first();

        return Inertia::render('Global/Invitations/Accept', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'name' => $invitation->name ?? ($resident ? $resident->full_name : null),
                'community_name' => $invitation->community->name,
                'role' => $invitation->role,
            ],
            'userExists' => $userExists,
            'isLoggedIn' => $isLoggedIn,
        ]);
    }

    /**
     * Process the acceptance.
     */
    public function store(Request $request)
    {
        $invitation = UserInvitation::where('token', $request->token)->with('community')->first();

        if (! $invitation || $invitation->status !== 'pending' || $invitation->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'token' => 'La invitación es inválida o ha expirado.',
            ]);
        }

        $userExists = User::where('email', $invitation->email)->exists();

        $rules = [
            'token' => 'required|string',
        ];

        if (! Auth::check()) {
            $rules['password'] = $userExists 
                ? 'required|string' 
                : 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string';
        }

        $request->validate($rules);



        $user = User::where('email', $invitation->email)->first();

        $resident = Resident::where('community_id', $invitation->community_id)
            ->where('email', $invitation->email)
            ->first();

        if ($user) {
            // Existing user: verify password if not logged in as this user
            if (! Auth::check() || Auth::user()->email !== $invitation->email) {
                if (! Auth::attempt(['email' => $invitation->email, 'password' => $request->password])) {
                    throw ValidationException::withMessages([
                        'password' => 'Contraseña incorrecta.',
                    ]);
                }
            }
            $user = Auth::user();
        } else {
            $user = User::create([
                'name' => $invitation->name ?? ($resident ? $resident->full_name : 'Usuario Invitado'),
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
            ]);

            Auth::login($user);
        }

        // Attach to community
        $user->communities()->syncWithoutDetaching([
            $invitation->community_id => [
                'role' => $invitation->role,
                'unit_id' => $invitation->unit_id,
                'invited_by_user_id' => $invitation->invited_by,
                'resident_role' => $invitation->resident_role,
            ],
        ]);

        // Update or Create Resident
        if ($resident) {
            if (! $resident->user_id) {
                $resident->update(['user_id' => $user->id, 'is_active' => true]);
            }
        } else {
            Resident::create([
                'community_id' => $invitation->community_id,
                'unit_id' => $invitation->unit_id,
                'user_id' => $user->id,
                'full_name' => $user->name ?? $invitation->name ?? 'Usuario Invitado',
                'email' => $user->email,
                'resident_type' => $invitation->resident_role === 'tenant' ? ResidentType::TENANT : ResidentType::DEPENDENT,
                'pays_administration' => false,
                'is_active' => true,
            ]);
        }

        // Update invitation status
        $invitation->update([
            'status' => 'accepted',
        ]);

        if (in_array($invitation->role, ['tenant_admin', 'sub_admin', 'accountant', 'auditor', 'guard'])) {
            return \Inertia\Inertia::location(route('tenant.admin.dashboard', ['community_slug' => $invitation->community->slug]));
        }

        return \Inertia\Inertia::location(route('tenant.resident.dashboard', ['community_slug' => $invitation->community->slug]));
    }
}
