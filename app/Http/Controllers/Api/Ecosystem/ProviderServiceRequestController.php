<?php

namespace App\Http\Controllers\Api\Ecosystem;

use App\Actions\Ecosystem\ServiceRequests\CancelServiceRequestAction;
use App\Actions\Ecosystem\ServiceRequests\CreateServiceRequestAction;
use App\Enums\CommunityRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ecosystem\StoreProviderServiceRequestRequest;
use App\Http\Resources\Ecosystem\ProviderServiceRequestResource;
use App\Models\ProviderServiceRequest;
use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ProviderServiceRequestController extends Controller
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ProviderServiceRequest::class);

        $community = $this->tenantContext->require();
        $user = $request->user();

        $query = ProviderServiceRequest::with(['provider', 'resident'])
            ->latest();

        // If Resident, restrict to their own
        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            $resident = Resident::where('user_id', $user->id)
                ->where('community_id', $community->id)
                ->firstOrFail();

            $query->where('resident_id', $resident->id);
        }

        return ProviderServiceRequestResource::collection($query->paginate(20));
    }

    public function store(
        StoreProviderServiceRequestRequest $request,
        CreateServiceRequestAction $action
    ): ProviderServiceRequestResource {
        Gate::authorize('create', ProviderServiceRequest::class);

        $community = $this->tenantContext->require();

        // Infer the resident_id securely
        $resident = Resident::where('user_id', $request->user()->id)
            ->where('community_id', $community->id)
            ->firstOrFail();

        $serviceRequest = $action->execute($resident, $request->validated());

        $serviceRequest->load(['provider', 'resident']);

        return new ProviderServiceRequestResource($serviceRequest);
    }

    public function show(string $community_slug, string $serviceRequest): ProviderServiceRequestResource
    {
        $serviceRequestModel = ProviderServiceRequest::findOrFail($serviceRequest);
        Gate::authorize('view', $serviceRequestModel);

        $serviceRequestModel->load(['provider', 'resident']);

        return new ProviderServiceRequestResource($serviceRequestModel);
    }

    public function cancel(string $community_slug, string $serviceRequest, CancelServiceRequestAction $action): ProviderServiceRequestResource
    {
        $serviceRequestModel = ProviderServiceRequest::findOrFail($serviceRequest);
        Gate::authorize('update', $serviceRequestModel);

        $serviceRequestModel = $action->execute($serviceRequestModel);

        $serviceRequestModel->load(['provider', 'resident']);

        return new ProviderServiceRequestResource($serviceRequestModel);
    }
}
