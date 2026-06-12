<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCommunityRole
{
    public function __construct(protected TenantContext $context) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $community = $this->context->get();

        if (! $community) {
            abort(403, 'No active community context.');
        }

        $user = $request->user();
        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        $userRole = $user->roleInCommunity($community)?->value;

        if (! $userRole || ! in_array($userRole, $roles)) {
            abort(403, 'Unauthorized. You do not have the required role.');
        }

        return $next($request);
    }
}
