<?php

namespace App\Http\Middleware;

use App\Models\FinancialSetting;
use App\Models\Resident;
use App\Models\SystemTaxonomy;
use App\Services\Financial\DunningService;
use App\Services\ModuleRegistryService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        $registryService = app(ModuleRegistryService::class);
        $navigationMenu = $registryService->getAuthorizedModules($activeCommunity, $activeRole);

        if ($activeRole === 'resident' && $user && $activeCommunity) {
            $pivot = DB::table('community_user')
                ->where('user_id', $user->id)
                ->where('community_id', $activeCommunity->id)
                ->first();

            if ($pivot && in_array($pivot->resident_role, ['family', 'familiar'])) {
                foreach ($navigationMenu as &$group) {
                    if (isset($group['items'])) {
                        $group['items'] = array_filter($group['items'], function ($item) {
                            return $item['key'] !== 'access';
                        });
                        $group['items'] = array_values($group['items']);
                    }
                }
                // Remove empty groups
                $navigationMenu = array_filter($navigationMenu, function ($group) {
                    return count($group['items']) > 0;
                });
                $navigationMenu = array_values($navigationMenu);
            }
        }

        $tenantId = $activeCommunity?->id ?? 'global';
        $taxonomies = Cache::remember("taxonomies_tenant_{$tenantId}", now()->addDay(), function () {
            return SystemTaxonomy::forCurrentTenantOrGlobal()
                ->get(['type', 'label', 'value', 'meta'])
                ->groupBy('type')
                ->toArray();
        });

        // Build dunning context for resident users.
        $dunning = [
            'is_restricted' => false,
            'restricted_modules' => [],
            'total_overdue' => 0.0,
            'oldest_due_date' => null,
        ];

        if ($activeRole === 'resident' && $user && $activeCommunity) {
            $resident = Resident::where('community_id', $activeCommunity->id)
                ->where('user_id', $user->id)
                ->with('unit')
                ->first();

            if ($resident && $resident->unit) {
                $setting = FinancialSetting::where('community_id', $activeCommunity->id)->first();

                if ($setting && $setting->hasDunningEnabled()) {
                    $dunningService = app(DunningService::class);
                    $dunning = $dunningService->getRestrictionContext($resident->unit, $setting);
                }
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'unreadNotificationsCount' => $request->user() ? $request->user()->unreadNotifications()->count() : 0,
            'unreadNotifications' => $request->user() ? $request->user()->unreadNotifications()->take(5)->get() : [],
            'tenant' => [
                'community' => $activeCommunity,
                'role' => $activeRole,
                'authorized_communities' => $authorizedCommunities,
            ],
            'navigation_menu' => $navigationMenu,
            'taxonomies' => $taxonomies,
            'dunning' => $dunning,
        ];
    }
}
