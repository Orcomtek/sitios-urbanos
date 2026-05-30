<?php

namespace App\Http\Middleware;

use App\Facades\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $community = TenantContext::getCommunity();

        if (! $community || ! $community->hasFeature($feature)) {
            abort(403, "SaaS Feature '{$feature}' is not enabled for this community.");
        }

        return $next($request);
    }
}
