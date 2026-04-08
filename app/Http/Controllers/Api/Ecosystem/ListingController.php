<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Ecosystem;

use App\Actions\Ecosystem\CreateListingAction;
use App\Actions\Ecosystem\ModerateListingAction;
use App\Actions\Ecosystem\UpdateListingAction;
use App\Enums\CommunityRole;
use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Ecosystem\CreateListingRequest;
use App\Http\Requests\Api\Ecosystem\ModerateListingRequest;
use App\Http\Requests\Api\Ecosystem\UpdateListingRequest;
use App\Http\Resources\Api\Ecosystem\ListingResource;
use App\Models\Listing;
use App\Models\Resident;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListingController extends Controller
{
    public function __construct(protected TenantContext $context) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $community = $this->context->require();

        if ($user->hasRoleInCommunity($community, CommunityRole::Guard)) {
            abort(403, 'Guards cannot access listings.');
        }

        $query = Listing::with('resident')->latest();

        $isAdmin = $user->hasRoleInCommunity($community, CommunityRole::Admin);

        if (! $isAdmin) {
            $resident = Resident::where('user_id', $user->id)
                ->where('community_id', $community->id)
                ->first();
            if (! $resident) {
                abort(403, 'Unauthorized.');
            }

            $query->where(function ($q) use ($resident) {
                $q->where('status', ListingStatus::Active->value)
                    ->orWhere('resident_id', $resident->id);
            });
        }

        return ListingResource::collection($query->paginate());
    }

    public function store(CreateListingRequest $request, CreateListingAction $action): ListingResource
    {
        $user = $request->user();
        $community = $this->context->require();

        if ($user->hasRoleInCommunity($community, CommunityRole::Guard)) {
            abort(403, 'Guards cannot access listings.');
        }

        $resident = Resident::where('user_id', $user->id)
            ->where('community_id', $community->id)
            ->where('is_active', true)
            ->first();

        if (! $resident) {
            abort(403, 'Only active residents can create listings.');
        }

        $listing = $action->execute($resident, $request->validated());

        return new ListingResource($listing->load('resident'));
    }

    public function show(Request $request, string $community_slug, Listing $listing): ListingResource
    {
        $user = $request->user();
        $community = $this->context->require();

        if ($user->hasRoleInCommunity($community, CommunityRole::Guard)) {
            abort(403, 'Guards cannot access listings.');
        }

        $isAdmin = $user->hasRoleInCommunity($community, CommunityRole::Admin);

        if (! $isAdmin) {
            $resident = Resident::where('user_id', $user->id)
                ->where('community_id', $community->id)
                ->first();
            if (! $resident) {
                abort(403, 'Unauthorized.');
            }

            if ($listing->resident_id !== $resident->id && $listing->status !== ListingStatus::Active) {
                abort(404, 'Listing not accessible.');
            }
        }

        return new ListingResource($listing->load('resident'));
    }

    public function update(UpdateListingRequest $request, string $community_slug, Listing $listing, UpdateListingAction $action): ListingResource
    {
        $user = $request->user();
        $community = $this->context->require();

        $resident = Resident::where('user_id', $user->id)
            ->where('community_id', $community->id)
            ->first();
        if (! $resident || $listing->resident_id !== $resident->id) {
            abort(403, 'Unauthorized to update this listing.');
        }

        $validated = $request->validated();

        if (isset($validated['status']) && in_array($validated['status'], [ListingStatus::Reported->value, ListingStatus::Removed->value])) {
            abort(403, 'Residents cannot report or remove their own listing directly via update.');
        }

        $listing = $action->execute($resident, $listing, $validated);

        return new ListingResource($listing->load('resident'));
    }

    public function moderate(ModerateListingRequest $request, string $community_slug, Listing $listing, ModerateListingAction $action): ListingResource
    {
        $user = $request->user();
        $community = $this->context->require();

        if (! $user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            abort(403, 'Only admins can moderate listings.');
        }

        $listing = $action->execute($listing, ListingStatus::from($request->input('status')));

        return new ListingResource($listing->load('resident'));
    }
}
