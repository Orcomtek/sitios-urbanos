<?php

namespace App\Http\Controllers\Api\Security;

use App\Actions\Security\TransitionVisitorStatusAction;
use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\VisitorResource;
use App\Models\Resident;
use App\Models\Visitor;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class VisitorController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $community = $this->context->require();

        $query = Visitor::query()->with(['unit', 'creator']);

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            $userUnitIds = Resident::where('user_id', $user->id)
                ->pluck('unit_id');

            $query->whereIn('unit_id', $userUnitIds);
        }

        return VisitorResource::collection($query->latest()->paginate());
    }

    public function store(Request $request): VisitorResource
    {
        $community = $this->context->require();

        $validated = $request->validate([
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where('community_id', $community->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:visitor,delivery,service,other'],
            'expected_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $isAdmin = $user->hasRoleInCommunity($community, CommunityRole::Admin);

        if (! $isAdmin) {
            $isResident = Resident::where('user_id', $user->id)
                ->where('unit_id', $validated['unit_id'])
                ->exists();

            if (! $isResident) {
                abort(403, 'No tienes permiso para registrar visitantes en esta unidad.');
            }
        }

        $visitor = Visitor::create([
            'unit_id' => $validated['unit_id'],
            'created_by' => $user->id,
            'name' => $validated['name'],
            'document_number' => $validated['document_number'] ?? null,
            'type' => $validated['type'],
            'status' => 'pending',
            'expected_at' => $validated['expected_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return new VisitorResource($visitor->load(['unit', 'creator']));
    }

    public function show(Request $request, string $community_slug, Visitor $visitor): VisitorResource
    {
        $user = $request->user();
        $community = $this->context->require();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            $isResident = Resident::where('user_id', $user->id)
                ->where('unit_id', $visitor->unit_id)
                ->exists();

            if (! $isResident) {
                abort(403, 'No tienes permiso para ver este visitante.');
            }
        }

        return new VisitorResource($visitor->load(['unit', 'creator']));
    }

    public function enter(Request $request, string $community_slug, Visitor $visitor, TransitionVisitorStatusAction $action): VisitorResource
    {
        $user = $request->user();
        $community = $this->context->require();

        $visitor = $action->execute($visitor, 'entered', $user, $community);

        return new VisitorResource($visitor->load(['unit', 'creator']));
    }

    public function exit(Request $request, string $community_slug, Visitor $visitor, TransitionVisitorStatusAction $action): VisitorResource
    {
        $user = $request->user();
        $community = $this->context->require();

        $visitor = $action->execute($visitor, 'exited', $user, $community);

        return new VisitorResource($visitor->load(['unit', 'creator']));
    }
}
