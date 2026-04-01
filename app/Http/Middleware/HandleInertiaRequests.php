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

        if ($activeCommunity && $request->user()) {
            $activeRole = $request->user()->roleInCommunity($activeCommunity)?->value;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'tenant' => [
                'community' => $activeCommunity,
                'role' => $activeRole,
            ],
        ];
    }
}
