<?php

namespace App\Http\Controllers\Api\Cockpit;

use App\Actions\Cockpit\GetOperationalWorkQueueAction;
use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\WorkQueueResource;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class WorkQueueController extends Controller
{
    /**
     * Get the operational work queue tailored to the user's role.
     */
    public function index(Request $request, TenantContext $tenantContext, GetOperationalWorkQueueAction $action)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $community = $tenantContext->require();
        $role = $user->roleInCommunity($community);

        if (!$role || $role === CommunityRole::Resident) {
            abort(403, 'Access denied to the work queue.');
        }

        $data = $action->execute($role);

        return new WorkQueueResource($data);
    }
}
