<?php

namespace App\Policies;

use App\Enums\CommunityRole;
use App\Enums\ServiceRequestStatus;
use App\Models\ProviderServiceRequest;
use App\Models\Resident;
use App\Models\User;
use App\Services\TenantContext;

class ProviderServiceRequestPolicy
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function viewAny(User $user): bool
    {
        $community = $this->tenantContext->require();

        return $user->hasRoleInCommunity($community, CommunityRole::Admin, CommunityRole::Resident);
    }

    public function view(User $user, ProviderServiceRequest $providerServiceRequest): bool
    {
        $community = $this->tenantContext->require();

        if ($user->hasRoleInCommunity($community, CommunityRole::Admin)) {
            return true;
        }

        if ($user->hasRoleInCommunity($community, CommunityRole::Resident)) {
            $resident = Resident::where('user_id', $user->id)
                ->where('community_id', $community->id)
                ->first();

            return $resident && $providerServiceRequest->resident_id === $resident->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        $community = $this->tenantContext->require();

        return $user->hasRoleInCommunity($community, CommunityRole::Resident);
    }

    public function update(User $user, ProviderServiceRequest $providerServiceRequest): bool
    {
        $community = $this->tenantContext->require();

        if ($user->hasRoleInCommunity($community, CommunityRole::Resident)) {
            $resident = Resident::where('user_id', $user->id)
                ->where('community_id', $community->id)
                ->first();

            if (! $resident || $providerServiceRequest->resident_id !== $resident->id) {
                return false;
            }

            return $providerServiceRequest->status === ServiceRequestStatus::PENDING;
        }

        return false;
    }
}
