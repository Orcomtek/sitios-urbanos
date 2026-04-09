<?php

namespace App\Http\Controllers\Api\Ecosystem;

use App\Actions\Ecosystem\DeleteProviderAction;
use App\Actions\Ecosystem\RegisterProviderAction;
use App\Actions\Ecosystem\UpdateProviderAction;
use App\Enums\CommunityRole;
use App\Enums\ProviderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ecosystem\StoreProviderRequest;
use App\Http\Requests\Ecosystem\UpdateProviderRequest;
use App\Http\Resources\Ecosystem\ProviderResource;
use App\Models\Provider;
use App\Services\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProviderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Provider::class);

        // Fetch providers, optionally filtering for only active ones based on Policy or logic
        // The policy 'viewAny' allows viewing, but to respect the rule:
        // "Resident/Guard: Read-only access to active providers" we should filter the query based on capability.
        // Easiest is to just fetch all, and let policy handle individual views,
        // but for a listing, we should pre-filter so Residents don't see inactive ones in the index.

        $user = request()->user();
        $communityId = app(TenantContext::class)->get()?->id;
        $role = $user->communities()->where('community_id', $communityId)->first()?->pivot->role;

        $query = Provider::query();

        if ($role !== CommunityRole::Admin->value) {
            $query->where('status', ProviderStatus::ACTIVE->value);
        }

        return ProviderResource::collection($query->latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProviderRequest $request, RegisterProviderAction $action): ProviderResource
    {
        $this->authorize('create', Provider::class);

        $provider = $action->execute($request->validated());

        return new ProviderResource($provider);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $communitySlug, string $provider): ProviderResource
    {
        $providerModel = Provider::findOrFail($provider);
        $this->authorize('view', $providerModel);

        return new ProviderResource($providerModel);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProviderRequest $request, UpdateProviderAction $action, string $communitySlug, string $provider): ProviderResource
    {
        $providerModel = Provider::findOrFail($provider);
        $this->authorize('update', $providerModel);

        $updatedProvider = $action->execute($providerModel, $request->validated());

        return new ProviderResource($updatedProvider);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteProviderAction $action, string $communitySlug, string $provider): Response
    {
        $providerModel = Provider::findOrFail($provider);
        $this->authorize('delete', $providerModel);

        $action->execute($providerModel);

        return response()->noContent();
    }
}
