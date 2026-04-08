<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use App\Models\Resident;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CockpitController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function dashboard(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::Admin, CommunityRole::Guard]);

        return Inertia::render('Cockpit/Dashboard');
    }

    public function workQueue(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::Admin, CommunityRole::Guard]);

        return Inertia::render('Cockpit/WorkQueue');
    }

    public function adminWorkQueue(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::Admin]);

        return Inertia::render('Cockpit/AdminWorkQueue');
    }

    public function resident(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::Resident]);

        return Inertia::render('Cockpit/ResidentCockpit');
    }

    public function residentPqrs(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::Resident]);

        return Inertia::render('Cockpit/ResidentPqrs');
    }

    public function residentOperations(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::Resident]);

        $community = $this->context->require();
        $user = $request->user();

        // Get resident's active units with their names/identifiers for the UI selectors
        $activeUnits = Resident::with('unit')
            ->where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->pluck('unit');

        return Inertia::render('Cockpit/ResidentOperations', [
            'activeUnits' => $activeUnits,
        ]);
    }

    /**
     * Helper to authorize access based on roles array.
     */
    protected function authorizeAccess(Request $request, array $allowedRoles): void
    {
        $community = $this->context->require();
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $role = $user->roleInCommunity($community);

        if (! $role || ! in_array($role, $allowedRoles, true)) {
            abort(403, 'Acceso denegado: Rol no autorizado para este módulo.');
        }
    }
}
