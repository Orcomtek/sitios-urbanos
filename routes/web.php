<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

$centralDomain = config('app.central_domain');

Route::domain($centralDomain)->group(function () {
    Route::get('/', function () {
        return redirect()->route('communities.index');
    })->middleware(['auth']);

    Route::middleware('auth')->group(function () {
        Route::get('/comunidades', [CommunityController::class, 'index'])->name('communities.index');
        Route::get('/comunidades/{slug}/ingresar', [CommunityController::class, 'enter'])->name('communities.enter');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});

use App\Http\Controllers\Tenant\ResidentController;
use App\Http\Controllers\Tenant\UnitController;
use App\Http\Middleware\TenantMiddleware;

Route::domain('{community_slug}.'.$centralDomain)
    ->middleware(['auth', TenantMiddleware::class])
    ->group(function () {
        Route::get('/', function (string $communitySlug) {
            return redirect()->route('units.index', ['community_slug' => $communitySlug]);
        })->name('tenant.dashboard');

        Route::post('/logout', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return \Inertia\Inertia::location(route('login'));
        })->name('tenant.logout');

        Route::resource('units', UnitController::class);
        Route::resource('residents', ResidentController::class);

        Route::prefix('cockpit')->middleware('auth')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Tenant\CockpitController::class, 'dashboard'])->name('tenant.cockpit.dashboard');
            Route::get('/work-queue', [\App\Http\Controllers\Tenant\CockpitController::class, 'workQueue'])->name('tenant.cockpit.work-queue');
            Route::get('/admin-work-queue', [\App\Http\Controllers\Tenant\CockpitController::class, 'adminWorkQueue'])->name('tenant.cockpit.admin-work-queue');
        });
    });
