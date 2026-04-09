<?php

namespace App\Policies;

use App\Enums\CommunityRole;
use App\Enums\ProviderStatus;
use App\Models\Provider;
use App\Models\User;
use App\Services\TenantContext;

class ProviderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $communityId = app(TenantContext::class)->get()?->id;

        return $communityId && $user->communities()->where('community_id', $communityId)->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Provider $provider): bool
    {
        $communityId = app(TenantContext::class)->get()?->id;

        // Must belong to the same community as current context
        if (! $communityId || $provider->community_id !== $communityId) {
            return false;
        }

        // Must be attached to this community
        $role = $user->communities()->where('community_id', $communityId)->first()?->pivot->role;

        if (! $role) {
            return false;
        }

        // Admin can view all providers (including inactive)
        if ($role === CommunityRole::Admin->value) {
            return true;
        }

        // Residents and Guards can only view active providers
        return $provider->status === ProviderStatus::ACTIVE;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $communityId = app(TenantContext::class)->get()?->id;
        if (! $communityId) {
            return false;
        }

        $role = $user->communities()->where('community_id', $communityId)->first()?->pivot->role;

        return $role === CommunityRole::Admin->value;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Provider $provider): bool
    {
        $communityId = app(TenantContext::class)->get()?->id;
        if (! $communityId || $provider->community_id !== $communityId) {
            return false;
        }

        $role = $user->communities()->where('community_id', $communityId)->first()?->pivot->role;

        return $role === CommunityRole::Admin->value;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Provider $provider): bool
    {
        $communityId = app(TenantContext::class)->get()?->id;
        if (! $communityId || $provider->community_id !== $communityId) {
            return false;
        }

        $role = $user->communities()->where('community_id', $communityId)->first()?->pivot->role;

        return $role === CommunityRole::Admin->value;
    }
}
