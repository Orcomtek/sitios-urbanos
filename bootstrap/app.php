<?php

use App\Http\Middleware\EnsureTenantHasFeature;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            require __DIR__.'/../routes/tenant.php';
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->priority([
            EnsureFrontendRequestsAreStateful::class,
            HandlePrecognitiveRequests::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            AuthenticatesRequests::class,
            ThrottleRequests::class,
            ThrottleRequestsWithRedis::class,
            AuthenticatesSessions::class,
            TenantMiddleware::class,
            SubstituteBindings::class,
            Authorize::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'tenant.feature' => EnsureTenantHasFeature::class,
            'role' => \App\Http\Middleware\CheckCommunityRole::class,
        ]);

        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            $primaryCommunity = $user?->communities()->first();

            if ($primaryCommunity) {
                $role = $primaryCommunity->pivot->role;
                if ($role === 'resident') {
                    return route('tenant.resident.dashboard', ['community_slug' => $primaryCommunity->slug]);
                }
                return route('tenant.admin.dashboard', ['community_slug' => $primaryCommunity->slug]);
            }

            return route('communities.index');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
