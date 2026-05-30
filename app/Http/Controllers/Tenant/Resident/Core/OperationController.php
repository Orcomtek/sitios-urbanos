<?php

namespace App\Http\Controllers\Tenant\Resident\Core;

use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperationController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::Resident]);

        $community = $this->context->require();
        $user = $request->user();

        $activeUnits = Resident::with('unit')
            ->where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->pluck('unit');

        return Inertia::render('Tenant/Resident/Core/Operations', [
            'activeUnits' => $activeUnits,
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
