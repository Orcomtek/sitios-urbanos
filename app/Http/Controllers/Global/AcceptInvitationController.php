<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\UserInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AcceptInvitationController extends Controller
{
    /**
     * Show the acceptance form.
     */
    public function show(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            abort(404, 'Token de invitación no proporcionado.');
        }

        $invitation = UserInvitation::where('token', $token)->with('community')->first();

        if (!$invitation || $invitation->status !== 'pending' || $invitation->expires_at->isPast()) {
            abort(403, 'La invitación es inválida o ha expirado.');
        }

        $userExists = User::where('email', $invitation->email)->exists();
        $isLoggedIn = Auth::check();

        return Inertia::render('Global/Invitations/Accept', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
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
        $request->validate([
            'token' => 'required|string',
            'name' => 'nullable|string|max:255',
            'password' => Auth::check() ? 'nullable|string' : 'required|string|min:8',
        ]);

        $invitation = UserInvitation::where('token', $request->token)->with('community')->first();

        if (!$invitation || $invitation->status !== 'pending' || $invitation->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'token' => 'La invitación es inválida o ha expirado.',
            ]);
        }

        $user = User::where('email', $invitation->email)->first();

        if ($user) {
            // Existing user: verify password if not logged in as this user
            if (!Auth::check() || Auth::user()->email !== $invitation->email) {
                if (!Auth::attempt(['email' => $invitation->email, 'password' => $request->password])) {
                    throw ValidationException::withMessages([
                        'password' => 'Contraseña incorrecta.',
                    ]);
                }
            }
            $user = Auth::user();
        } else {
            // New user: validate name and create
            if (!$request->name) {
                throw ValidationException::withMessages([
                    'name' => 'El nombre es obligatorio.',
                ]);
            }

            $user = User::create([
                'name' => $request->name,
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
            ]
        ]);

        // Update invitation status
        $invitation->update([
            'status' => 'accepted',
        ]);

        // Redirección dinámica al Tenant Runtime
        $scheme = $request->getScheme();
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'sitiosurbanos.test';
        
        return redirect()->to($scheme . '://' . $invitation->community->slug . '.' . $host);
    }
}
