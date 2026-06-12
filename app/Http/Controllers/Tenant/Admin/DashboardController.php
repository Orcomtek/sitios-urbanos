<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request): Response
    {
        $this->authorizeAccess($request, [
            CommunityRole::TenantAdmin,
            CommunityRole::SubAdmin,
            CommunityRole::Accountant,
            CommunityRole::Auditor,
            CommunityRole::Guard,
        ]);

        $community = $this->context->require();
        $totalCoefficient = \App\Models\Unit::where('community_id', $community->id)->sum('coefficient');
        $hasZeroOrNullCoefficient = \App\Models\Unit::where('community_id', $community->id)
            ->where(function($query) {
                $query->whereNull('coefficient')->orWhere('coefficient', '<=', 0);
            })->exists();

        return Inertia::render('Tenant/Admin/Dashboard', [
            'matrix_status' => [
                'total_coefficient' => (float) $totalCoefficient,
                'has_warnings' => ($totalCoefficient != 1.0 && $totalCoefficient != \App\Models\Unit::where('community_id', $community->id)->count()) || $hasZeroOrNullCoefficient,
                'is_flat_fee' => $totalCoefficient == \App\Models\Unit::where('community_id', $community->id)->count()
            ],
            'is_admin' => $request->user()->roleInCommunity($community) === CommunityRole::TenantAdmin
        ]);
    }

    public function workQueue(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::TenantAdmin, CommunityRole::Guard]);

        return Inertia::render('Tenant/Admin/Core/WorkQueue');
    }

    public function adminWorkQueue(Request $request): Response
    {
        $this->authorizeAccess($request, [CommunityRole::TenantAdmin]);

        return Inertia::render('Tenant/Admin/Core/AdminWorkQueue');
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
