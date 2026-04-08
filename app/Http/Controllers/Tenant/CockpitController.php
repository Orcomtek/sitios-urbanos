<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Services\TenantContext;
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

    /**
     * Helper to authorize access based on roles array.
     */
    protected function authorizeAccess(Request $request, array $allowedRoles): void
    {
        $community = $this->context->require();
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $role = $user->roleInCommunity($community);

        if (!$role || !in_array($role, $allowedRoles, true)) {
            abort(403, 'Acceso denegado: Rol no autorizado para este módulo.');
        }
    }
}
