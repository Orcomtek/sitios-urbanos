<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenantContext = app(TenantContext::class);
        $activeCommunity = $tenantContext->get();
        $activeRole = null;
        $authorizedCommunities = [];

        if ($user = $request->user()) {
            $user->loadMissing('communities');

            if ($activeCommunity) {
                $activeRole = $user->roleInCommunity($activeCommunity)?->value;
            }

            $authorizedCommunities = $user->communities->map(fn ($community) => [
                'id' => $community->id,
                'name' => $community->name,
                'slug' => $community->slug,
                'role' => $community->pivot->role,
            ])->toArray();
        }

        $registryService = app(\App\Services\ModuleRegistryService::class);
        $navigationMenu = $registryService->getAuthorizedModules($activeCommunity, $activeRole);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'tenant' => [
                'community' => $activeCommunity,
                'role' => $activeRole,
                'authorized_communities' => $authorizedCommunities,
            ],
            'navigation_menu' => $navigationMenu,
        ];
    }
}
