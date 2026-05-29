<?php

use App\Http\Controllers\Tenant\Admin\Core\ResidentController;
use App\Http\Controllers\Tenant\Admin\Core\UnitController;
use App\Http\Controllers\Tenant\Admin\Core\UnitGeneratorController;
use App\Http\Controllers\Tenant\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Tenant\Admin\Ecosystem\ProviderController as AdminProviderController;
use App\Http\Controllers\Tenant\Resident\DashboardController as ResidentDashboardController;
use App\Http\Controllers\Tenant\Resident\Core\OperationController;
use App\Http\Controllers\Tenant\Resident\Ecosystem\EcosystemController;
use App\Http\Controllers\Tenant\Resident\Ecosystem\ProviderController as ResidentProviderController;
use App\Http\Controllers\Tenant\Resident\Governance\PollController;
use App\Http\Controllers\Tenant\Resident\Governance\PqrsController;
use App\Http\Controllers\Tenant\Resident\ActivityController;
use App\Actions\Governance\SubmitPollVoteAction;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$centralDomain = config('app.central_domain');

Route::domain('{community_slug}.'.$centralDomain)
    ->middleware(['web', 'auth', TenantMiddleware::class])
    ->group(function () {
        Route::get('/', function (string $communitySlug) {
            return redirect()->route('tenant.admin.core.units.index', ['community_slug' => $communitySlug]);
        })->name('tenant.dashboard');

        Route::post('/logout', function (Request $request) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Inertia::location(route('login'));
        })->name('tenant.logout');

        Route::prefix('admin')->name('tenant.admin.')->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            
            Route::prefix('core')->name('core.')->group(function () {
                Route::get('/work-queue', [AdminDashboardController::class, 'workQueue'])->name('work-queue');
                Route::get('/admin-work-queue', [AdminDashboardController::class, 'adminWorkQueue'])->name('admin-work-queue');
                
                Route::prefix('units')->name('units.')->group(function () {
                    Route::get('/generator', [UnitGeneratorController::class, 'index'])->name('generator');
                    Route::post('/generator/generate', [UnitGeneratorController::class, 'generate'])->name('generator.generate');
                    Route::post('/generator/bulk-amenities', [UnitGeneratorController::class, 'bulkUpdateAmenities'])->name('generator.bulk-amenities');
                });
                Route::resource('units', UnitController::class);
                Route::resource('residents', ResidentController::class);
            });

            Route::prefix('ecosystem')->name('ecosystem.')->group(function () {
                Route::get('/providers', [AdminProviderController::class, 'index'])->name('providers');
            });
        });

        Route::prefix('resident')->name('tenant.resident.')->group(function () {
            Route::get('/dashboard', [ResidentDashboardController::class, 'index'])->name('dashboard');
            Route::get('/activity', [ActivityController::class, 'index'])->name('activity');

            Route::prefix('core')->name('core.')->group(function () {
                Route::get('/operations', [OperationController::class, 'index'])->name('operations');
            });

            Route::prefix('governance')->name('governance.')->group(function () {
                Route::get('/pqrs', [PqrsController::class, 'index'])->name('pqrs');
                Route::get('/polls', [PollController::class, 'index'])->name('polls.index');
                Route::post('/polls/{poll}/vote', [SubmitPollVoteAction::class, 'handle'])->name('polls.vote');
            });

            Route::prefix('ecosystem')->name('ecosystem.')->group(function () {
                Route::get('/', [EcosystemController::class, 'index'])->name('index');
                Route::get('/providers', [ResidentProviderController::class, 'index'])->name('providers');
            });
        });
    });
