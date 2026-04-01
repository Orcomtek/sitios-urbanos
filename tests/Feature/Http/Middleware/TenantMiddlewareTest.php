<?php

use App\Actions\ResolveUserCommunityBySlugAction;
use App\Http\Middleware\TenantMiddleware;
use App\Models\Community;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('throws a logic exception if community_slug route parameter is missing', function () {
    $middleware = new TenantMiddleware(
        app(ResolveUserCommunityBySlugAction::class),
        app(TenantContext::class)
    );

    $request = Request::create('/test', 'GET');
    $request->setRouteResolver(fn () => (new Route('GET', '/test', []))->bind($request));

    expect(fn () => $middleware->handle($request, fn () => response('ok')))
        ->toThrow(LogicException::class, 'TenantMiddleware applied to a route without a community_slug parameter.');
});

it('throws a not found http exception if community resolution fails', function () {
    $resolverMock = Mockery::mock(ResolveUserCommunityBySlugAction::class);
    $resolverMock->shouldReceive('execute')
        ->once()
        ->andThrow(new ModelNotFoundException);

    $middleware = new TenantMiddleware(
        $resolverMock,
        app(TenantContext::class)
    );

    $request = Request::create('/test/slug-123', 'GET');
    $route = new Route('GET', '/test/{community_slug}', []);
    $route->bind($request);
    $route->setParameter('community_slug', 'slug-123');
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => User::factory()->make());

    expect(fn () => $middleware->handle($request, fn () => response('ok')))
        ->toThrow(NotFoundHttpException::class);
});

it('resolves community, populates context, and calls next middleware', function () {
    $user = User::factory()->make();
    $community = Community::factory()->make(['slug' => 'slug-123']);

    $resolverMock = Mockery::mock(ResolveUserCommunityBySlugAction::class);
    $resolverMock->shouldReceive('execute')
        ->once()
        ->with($user, 'slug-123')
        ->andReturn($community);

    $contextMock = Mockery::mock(TenantContext::class);
    $contextMock->shouldReceive('set')
        ->once()
        ->with($community);

    $middleware = new TenantMiddleware(
        $resolverMock,
        $contextMock
    );

    $request = Request::create('/test/slug-123', 'GET');
    $route = new Route('GET', '/test/{community_slug}', []);
    $route->bind($request);
    $route->setParameter('community_slug', 'slug-123');
    $request->setRouteResolver(fn () => $route);
    $request->setUserResolver(fn () => $user);

    $nextCalled = false;
    $response = $middleware->handle($request, function ($req) use (&$nextCalled) {
        $nextCalled = true;

        return response('ok');
    });

    expect($nextCalled)->toBeTrue()
        ->and($response->getContent())->toBe('ok');
});
