<?php

namespace App\Http\Controllers\Api\Security;

use App\Actions\Security\AcknowledgeEmergencyEventAction;
use App\Actions\Security\ResolveEmergencyEventAction;
use App\Actions\Security\TriggerEmergencyEventAction;
use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Security\EmergencyEventResource;
use App\Models\EmergencyEvent;
use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class EmergencyEventController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $community = $this->context->require();

        $query = EmergencyEvent::query()->with(['unit', 'triggerer']);

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            $userUnitIds = Resident::where('user_id', $user->id)
                ->pluck('unit_id');

            $query->whereIn('unit_id', $userUnitIds);
        }

        return EmergencyEventResource::collection($query->latest()->paginate());
    }

    public function store(Request $request, TriggerEmergencyEventAction $action): EmergencyEventResource
    {
        $community = $this->context->require();

        $validated = $request->validate([
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where('community_id', $community->id),
            ],
            'type' => ['required', 'in:panic,medical,security,other'],
            'description' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        // Must be a resident of the unit, or an admin to trigger for a unit
        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            $isResident = Resident::where('user_id', $user->id)
                ->where('unit_id', $validated['unit_id'])
                ->exists();

            if (! $isResident) {
                abort(403, 'No tienes permiso para registrar emergencias en esta unidad.');
            }
        }

        $unit = \App\Models\Unit::findOrFail($validated['unit_id']);
        
        $emergency = $action->execute($user, $unit, $validated);

        return new EmergencyEventResource($emergency->load(['unit', 'triggerer']));
    }

    public function show(Request $request, string $community_slug, EmergencyEvent $emergency): EmergencyEventResource
    {
        $user = $request->user();
        $community = $this->context->require();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            $isResident = Resident::where('user_id', $user->id)
                ->where('unit_id', $emergency->unit_id)
                ->exists();

            if (! $isResident) {
                abort(403, 'No tienes permiso para ver esta emergencia.');
            }
        }

        return new EmergencyEventResource($emergency->load(['unit', 'triggerer']));
    }

    public function acknowledge(Request $request, string $community_slug, EmergencyEvent $emergency, AcknowledgeEmergencyEventAction $action): EmergencyEventResource
    {
        $user = $request->user();
        $community = $this->context->require();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            abort(403, 'No tienes permiso para gestionar esta emergencia.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $emergency = $action->execute($user, $emergency, $validated);

        return new EmergencyEventResource($emergency->load(['unit', 'triggerer']));
    }

    public function resolve(Request $request, string $community_slug, EmergencyEvent $emergency, ResolveEmergencyEventAction $action): EmergencyEventResource
    {
        $user = $request->user();
        $community = $this->context->require();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            abort(403, 'No tienes permiso para gestionar esta emergencia.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $emergency = $action->execute($user, $emergency, $validated);

        return new EmergencyEventResource($emergency->load(['unit', 'triggerer']));
    }
}
