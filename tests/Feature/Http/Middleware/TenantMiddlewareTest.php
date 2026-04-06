<?php

use App\Http\Middleware\TenantMiddleware;
use App\Models\Community;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Define a dummy route with the correct parameter
    Route::domain('{community_slug}.' . config('app.central_domain'))
        ->get('/test', function () {
            $context = app(TenantContext::class);

            return 'ok: '.$context->require()->slug;
        })
        ->middleware(['web', TenantMiddleware::class])
        ->name('tenant.test');

    // Define a dummy route WITHOUT the parameter to test configuration failure
    Route::get('/invalid-route', function () {
        return 'ok';
    })->middleware(['web', TenantMiddleware::class])->name('invalid.test');

    Route::getRoutes()->refreshNameLookups();
});

it('allows access and resolves context for an active community where the user belongs', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);
    $user->communities()->attach($community, ['role' => 'admin']);

    $response = $this->actingAs($user)->get(route('tenant.test', ['community_slug' => $community->slug]));

    $response->assertOk();
    $response->assertSee("ok: {$community->slug}");

    // Also verify the context singleton holds the right community
    $context = app(TenantContext::class);
    expect($context->get()->id)->toBe($community->id);
});

it('returns 404 when user tries to access an existing community they do not belong to', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create(['status' => 'active']);

    $response = $this->actingAs($user)->get(route('tenant.test', ['community_slug' => $community->slug]));

    $response->assertNotFound();
});

it('returns 404 when the community slug does not exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('tenant.test', ['community_slug' => 'non-existent-slug']));

    $response->assertNotFound();
});

it('throws LogicException resulting in 500 when route is missing community_slug parameter', function () {
    $user = User::factory()->create();

    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($user)->get('/invalid-route'))
        ->toThrow(LogicException::class, 'TenantMiddleware applied to a route without a community_slug parameter.');
});
