<?php

namespace App\Http\Controllers\Api\Cockpit;

use App\Actions\Dashboard\GetOperationalDashboardAction;
use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\TenantContext;

class DashboardController extends Controller
{
    /**
     * Get the operational dashboard tailored to the user's role.
     */
    public function index(\Illuminate\Http\Request $request, TenantContext $tenantContext, GetOperationalDashboardAction $action)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $community = $tenantContext->require();
        $role = $user->roleInCommunity($community);

        if (!$role || $role === CommunityRole::Resident) {
            abort(403, 'Access denied to this dashboard.');
        }

        $data = $action->execute($role);

        return new DashboardResource($data);
    }
}
