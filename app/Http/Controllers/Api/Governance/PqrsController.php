<?php

namespace App\Http\Controllers\Api\Governance;

use App\Actions\Governance\UpdatePqrsStateAction;
use App\Enums\CommunityRole;
use App\Enums\PqrsStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePqrsRequest;
use App\Http\Requests\UpdatePqrsStateRequest;
use App\Http\Resources\PqrsResource;
use App\Models\Pqrs;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\Governance\PqrsCreatedNotification;
use App\Notifications\Governance\PqrsUpdatedNotification;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PqrsController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request, string $community_slug): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();
        $isAdmin = $user->hasRoleInCommunity($community, CommunityRole::Admin);

        $query = Pqrs::where('community_id', $community->id)->with('resident');

        if (! $isAdmin) {
            $resident = Resident::where('community_id', $community->id)
                ->where('user_id', $user->id)
                ->first();

            if (! $resident) {
                return response()->json(['data' => []]);
            }

            $query->where('resident_id', $resident->id);
        }

        $pqrs = $query->orderByDesc('created_at')->paginate(50);

        return response()->json(PqrsResource::collection($pqrs)->response()->getData(true));
    }

    public function store(StorePqrsRequest $request, string $community_slug): JsonResponse
    {
        $community = $this->context->require();
        $user = $request->user();

        $resident = Resident::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $resident) {
            return response()->json(['message' => 'User is not a resident in this community.'], 403);
        }

        $pqrs = Pqrs::create([
            'community_id' => $community->id,
            'resident_id' => $resident->id,
            'type' => $request->validated('type'),
            'subject' => $request->validated('subject'),
            'description' => $request->validated('description'),
            'is_anonymous' => $request->validated('is_anonymous'),
            'status' => PqrsStatus::OPEN,
        ]);

        // Notify admins
        $admins = User::whereHas('communities', function ($q) use ($community) {
            $q->where('community_id', $community->id)
                ->where('role', CommunityRole::Admin->value);
        })->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new PqrsCreatedNotification($pqrs));
        }

        return response()->json([
            'message' => 'PQRS created successfully',
            'data' => new PqrsResource($pqrs),
        ], 201);
    }

    public function show(Request $request, string $community_slug, string $pqrs_id): JsonResponse
    {
        $pqrs = Pqrs::findOrFail($pqrs_id);
        $community = $this->context->require();
        $user = $request->user();

        // Security check: Must belong to current community
        if ($pqrs->community_id !== $community->id) {
            abort(404);
        }

        $isAdmin = $user->hasRoleInCommunity($community, CommunityRole::Admin);

        $resident = Resident::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        $isOwner = $resident && $pqrs->resident_id === $resident->id;

        if (! $isAdmin && ! $isOwner) {
            abort(403, 'No tienes permiso para ver este PQRS.');
        }

        $pqrs->load('resident');

        return response()->json([
            'data' => new PqrsResource($pqrs),
        ]);
    }

    public function update_status(UpdatePqrsStateRequest $request, string $community_slug, string $pqrs_id, UpdatePqrsStateAction $action): JsonResponse
    {
        $pqrs = Pqrs::findOrFail($pqrs_id);
        $community = $this->context->require();
        $user = $request->user();

        if ($pqrs->community_id !== $community->id) {
            abort(404);
        }

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            abort(403, 'Solo administradores pueden actualizar el estado del PQRS.');
        }

        $pqrs = $action->execute(
            $pqrs,
            PqrsStatus::from($request->validated('status')),
            $user,
            $request->validated('admin_response')
        );

        // Notify resident if linked user exists
        $pqrs->load('resident.user');
        $residentUser = $pqrs->resident?->user;

        if ($residentUser) {
            $residentUser->notify(new PqrsUpdatedNotification($pqrs));
        }

        return response()->json([
            'message' => 'PQRS status updated successfully',
            'data' => new PqrsResource($pqrs),
        ]);
    }
}
