<?php

namespace App\Http\Controllers\Api\Cockpit;

use App\Actions\Cockpit\GetResidentCockpitAction;
use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidentCockpitController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request, GetResidentCockpitAction $action): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $role = $user->roleInCommunity($community);

        // Strict role access: Only residents (No Admin/Guard for this cockpit)
        if ($role !== CommunityRole::Resident) {
            abort(403, 'Acceso denegado: Cabina exclusiva para residentes.');
        }

        $data = $action->execute($user->id, $role);

        return response()->json(['data' => $data]);
    }
}
