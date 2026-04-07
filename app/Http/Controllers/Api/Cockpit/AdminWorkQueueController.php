<?php

namespace App\Http\Controllers\Api\Cockpit;

use App\Actions\Cockpit\GetAdminWorkQueueAction;
use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminWorkQueueResource;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class AdminWorkQueueController extends Controller
{
    /**
     * Get the operational work queue tailored to the admin role.
     */
    public function index(Request $request, TenantContext $tenantContext, GetAdminWorkQueueAction $action)
    {
        /** @var User $user */
        $user = $request->user();
        $community = $tenantContext->require();
        $role = $user->roleInCommunity($community);

        if ($role !== CommunityRole::Admin) {
            abort(403, 'Access denied to the admin work queue.');
        }

        $data = $action->execute($role);

        return new AdminWorkQueueResource($data);
    }
}
