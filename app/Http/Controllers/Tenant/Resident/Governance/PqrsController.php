<?php

namespace App\Http\Controllers\Tenant\Resident\Governance;

use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PqrsController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::Resident]);

        return Inertia::render('Tenant/Resident/Governance/Pqrs');
    }

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
