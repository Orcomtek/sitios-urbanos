<?php

namespace App\Http\Controllers\Api\Security;

use App\Actions\Security\TransitionPackageStatusAction;
use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $community = $this->context->require();

        $query = Package::query()->with(['unit', 'receiver', 'deliverer']);

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            $userUnitIds = Resident::where('user_id', $user->id)
                ->pluck('unit_id');

            $query->whereIn('unit_id', $userUnitIds);
        }

        return PackageResource::collection($query->latest()->paginate());
    }

    public function store(Request $request): PackageResource
    {
        $community = $this->context->require();
        $user = $request->user();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            abort(403, 'No tienes permiso para registrar paquetes en esta unidad.');
        }

        $validated = $request->validate([
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where('community_id', $community->id),
            ],
            'carrier' => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $package = Package::create([
            'unit_id' => $validated['unit_id'],
            'received_by' => $user->id,
            'carrier' => $validated['carrier'] ?? null,
            'tracking_number' => $validated['tracking_number'] ?? null,
            'sender_name' => $validated['sender_name'] ?? null,
            'recipient_name' => $validated['recipient_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => 'received',
            'received_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return new PackageResource($package->load(['unit', 'receiver']));
    }

    public function show(Request $request, string $community_slug, Package $package): PackageResource
    {
        $user = $request->user();
        $community = $this->context->require();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Guard)) {
            $isResident = Resident::where('user_id', $user->id)
                ->where('unit_id', $package->unit_id)
                ->exists();

            if (! $isResident) {
                abort(403, 'No tienes permiso para ver este paquete.');
            }
        }

        return new PackageResource($package->load(['unit', 'receiver', 'deliverer']));
    }

    public function deliver(Request $request, string $community_slug, Package $package, TransitionPackageStatusAction $action): PackageResource
    {
        $user = $request->user();
        $community = $this->context->require();

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $package = $action->execute($package, 'delivered', $user, $community, $validated['notes'] ?? null);

        return new PackageResource($package->load(['unit', 'receiver', 'deliverer']));
    }

    public function return(Request $request, string $community_slug, Package $package, TransitionPackageStatusAction $action): PackageResource
    {
        $user = $request->user();
        $community = $this->context->require();

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $package = $action->execute($package, 'returned', $user, $community, $validated['notes'] ?? null);

        return new PackageResource($package->load(['unit', 'receiver', 'deliverer']));
    }
}
