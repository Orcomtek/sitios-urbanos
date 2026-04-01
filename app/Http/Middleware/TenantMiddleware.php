<?php

namespace App\Http\Middleware;

use App\Actions\ResolveUserCommunityBySlugAction;
use App\Models\User;
use App\Services\TenantContext;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantMiddleware
{
    public function __construct(
        protected ResolveUserCommunityBySlugAction $resolver,
        protected TenantContext $context
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('community_slug');

        if (! $slug) {
            throw new LogicException('TenantMiddleware applied to a route without a community_slug parameter.');
        }

        /** @var User $user */
        $user = $request->user();

        try {
            $community = $this->resolver->execute($user, $slug);
        } catch (ModelNotFoundException $e) {
            // Mask the resolution failure as a generic 404 to prevent tenant enumeration
            throw new NotFoundHttpException('Community not found.', $e);
        }

        $this->context->set($community);

        return $next($request);
    }
}
