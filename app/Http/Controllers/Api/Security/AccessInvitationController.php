<?php

namespace App\Http\Controllers\Api\Security;

use App\Actions\Security\ConsumeAccessInvitationAction;
use App\Actions\Security\CreateAccessInvitationAction;
use App\Actions\Security\RevokeAccessInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccessInvitationRequest;
use App\Http\Resources\AccessInvitationResource;
use App\Models\AccessInvitation;
use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccessInvitationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantContext = app(TenantContext::class)->get();
        $user = $request->user();

        $query = AccessInvitation::with(['unit', 'visitor']);

        if ($user->hasRoleInCommunity($tenantContext, 'admin') || $user->hasRoleInCommunity($tenantContext, 'guard')) {
            // Admins/guards can see all invitations within the community.
        } elseif ($user->hasRoleInCommunity($tenantContext, 'resident')) {
            // Residents can only see their own active units' invitations.
            $unitIds = Resident::where('community_id', $tenantContext->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->pluck('unit_id');
            $query->whereIn('unit_id', $unitIds);
        } else {
            // No permissions, return empty set
            $query->whereRaw('1 = 0');
        }

        return AccessInvitationResource::collection($query->latest()->paginate());
    }

    public function store(
        StoreAccessInvitationRequest $request,
        CreateAccessInvitationAction $action
    ): JsonResponse {
        $tenantContext = app(TenantContext::class)->get();

        $invitation = $action->execute(
            $request->user(),
            $tenantContext,
            $request->validated()
        );

        return response()->json(
            new AccessInvitationResource($invitation),
            201
        );
    }

    public function show(Request $request, string $community_slug, AccessInvitation $invitation): AccessInvitationResource
    {
        $tenantContext = app(TenantContext::class)->get();
        $user = $request->user();

        if ($user->hasRoleInCommunity($tenantContext, 'resident') && ! $user->hasRoleInCommunity($tenantContext, 'admin') && ! $user->hasRoleInCommunity($tenantContext, 'guard')) {
            $hasAccess = Resident::where('community_id', $tenantContext->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where('unit_id', $invitation->unit_id)
                ->exists();

            if (! $hasAccess) {
                abort(403, 'Unauthorized access to this invitation.');
            }
        }

        return new AccessInvitationResource($invitation->load(['unit', 'visitor']));
    }

    public function revoke(
        Request $request,
        string $community_slug,
        AccessInvitation $invitation,
        RevokeAccessInvitationAction $action
    ): JsonResponse {
        $tenantContext = app(TenantContext::class)->get();
        $user = $request->user();

        if ($user->hasRoleInCommunity($tenantContext, 'resident') && ! $user->hasRoleInCommunity($tenantContext, 'admin') && ! $user->hasRoleInCommunity($tenantContext, 'guard')) {
            $hasAccess = Resident::where('community_id', $tenantContext->id)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->where('unit_id', $invitation->unit_id)
                ->exists();

            if (! $hasAccess) {
                abort(403, 'Unauthorized access to this invitation.');
            }
        }

        try {
            $action->execute($user, $invitation, 'Revoked manually via API.');

            return response()->json(['message' => 'Invitation revoked successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function consume(
        Request $request,
        string $community_slug,
        AccessInvitation $invitation,
        ConsumeAccessInvitationAction $action
    ): JsonResponse {
        $tenantContext = app(TenantContext::class)->get();
        $user = $request->user();

        // Only guards or admins can consume an invitation directly at the gate.
        if (! $user->hasRoleInCommunity($tenantContext, 'admin') && ! $user->hasRoleInCommunity($tenantContext, 'guard')) {
            abort(403, 'Unauthorized to consume access invitations.');
        }

        try {
            $action->execute($user, $invitation);

            return response()->json([
                'message' => 'Invitation consumed successfully.',
                'invitation' => new AccessInvitationResource($invitation->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
