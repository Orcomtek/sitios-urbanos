<?php

namespace App\Http\Controllers\Tenant\Resident\Ecosystem;

use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use App\Models\Resident;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EcosystemController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::Resident]);

        $user = $request->user();
        $community = $this->context->require();

        $resident = Resident::where('user_id', $user->id)
            ->where('community_id', $community->id)
            ->first();

        return Inertia::render('Tenant/Resident/Ecosystem/Index', [
            'resident_id' => $resident?->id,
        ]);
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
