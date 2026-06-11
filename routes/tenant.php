<?php

use App\Actions\Governance\SubmitPollVoteAction;
use App\Http\Controllers\Api\Cockpit\ActivityTimelineController;
use App\Http\Controllers\Api\Cockpit\AdminWorkQueueController;
use App\Http\Controllers\Api\Cockpit\DashboardController as ApiCockpitDashboardController;
use App\Http\Controllers\Api\Cockpit\NotificationController;
use App\Http\Controllers\Api\Cockpit\ResidentCockpitController;
use App\Http\Controllers\Api\Cockpit\WorkQueueController;
use App\Http\Controllers\Tenant\Admin\Core\BulkImportController;
use App\Http\Controllers\Tenant\Admin\Core\ResidentController;
use App\Http\Controllers\Tenant\Admin\Core\UnitController;
use App\Http\Controllers\Tenant\Admin\Core\UnitGeneratorController;
use App\Http\Controllers\Tenant\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Tenant\Admin\Ecosystem\ProviderController as AdminProviderController;
use App\Http\Controllers\Tenant\Admin\Financial\SettingController;
use App\Http\Controllers\Tenant\Admin\Governance\TicketController as AdminTicketController;
use App\Http\Controllers\Tenant\Admin\Security\RadarController;
use App\Http\Controllers\Tenant\Admin\Settings\TeamController;
use App\Http\Controllers\Tenant\Resident\AccessController;
use App\Http\Controllers\Tenant\Resident\ActivityController;
use App\Http\Controllers\Tenant\Resident\CensusController;
use App\Http\Controllers\Tenant\Resident\Core\OperationController;
use App\Http\Controllers\Tenant\Resident\DashboardController as ResidentDashboardController;
use App\Http\Controllers\Tenant\Resident\Ecosystem\EcosystemController;
use App\Http\Controllers\Tenant\Resident\Ecosystem\ProviderController as ResidentProviderController;
use App\Http\Controllers\Tenant\Resident\Governance\PollController;
use App\Http\Controllers\Tenant\Resident\MoveRequestController;
use App\Http\Controllers\Tenant\Resident\TicketController;
use App\Http\Middleware\TenantMiddleware;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$centralDomain = config('app.central_domain');

Route::domain('{community_slug}.'.$centralDomain)
    ->middleware(['web', 'auth', TenantMiddleware::class])
    ->group(function () {
        Route::get('/', function (string $communitySlug, Request $request) {
            $user = $request->user();
            $community = app(TenantContext::class)->get();
            $role = $user->roleInCommunity($community)?->value;

            if ($role === 'resident') {
                return redirect()->route('tenant.resident.dashboard', ['community_slug' => $communitySlug]);
            }

            return redirect()->route('tenant.admin.dashboard', ['community_slug' => $communitySlug]);
        })->name('tenant.dashboard');

        Route::post('/logout', function (Request $request) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Inertia::location(route('login'));
        })->name('tenant.logout');

        Route::prefix('_tenant/cockpit')->name('tenant.cockpit.')->group(function () {
            Route::get('/dashboard', [ApiCockpitDashboardController::class, 'index'])->name('dashboard');
            Route::get('/work-queue', [WorkQueueController::class, 'index'])->name('work-queue');
            Route::get('/admin-work-queue', [AdminWorkQueueController::class, 'index'])->name('admin-work-queue');
            Route::get('/resident', [ResidentCockpitController::class, 'index'])->name('resident');

            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
            Route::patch('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

            Route::get('/activity', [ActivityTimelineController::class, 'index'])->name('activity.index');
        });

        Route::prefix('admin')
            ->name('tenant.admin.')
            ->middleware('role:tenant_admin,sub_admin,accountant,guard')
            ->group(function () {
                Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

                Route::prefix('core')->name('core.')->group(function () {
                    Route::get('/work-queue', [AdminDashboardController::class, 'workQueue'])->name('work-queue');
                    Route::get('/admin-work-queue', [AdminDashboardController::class, 'adminWorkQueue'])->name('admin-work-queue');

                    Route::prefix('units')->name('units.')->group(function () {
                        Route::get('/generator', [UnitGeneratorController::class, 'index'])->name('generator');
                        Route::post('/generator/generate', [UnitGeneratorController::class, 'generate'])->name('generator.generate');
                        Route::post('/generator/bulk-amenities', [UnitGeneratorController::class, 'bulkUpdateAmenities'])->name('generator.bulk-amenities');
                    });
                    Route::post('residents/dispatch-invitations', [ResidentController::class, 'dispatchInvitations'])->name('residents.dispatch-invitations');
                    Route::resource('units', UnitController::class);
                    Route::resource('residents', ResidentController::class);

                    Route::get('/imports', [BulkImportController::class, 'index'])->name('imports.index');
                    Route::post('/imports', [BulkImportController::class, 'store'])->name('imports.store');
                    Route::get('/imports/{import}', [BulkImportController::class, 'show'])->name('imports.show');
                });

                Route::prefix('logistics')->name('logistics.')->group(function () {
                    Route::get('/moves', [App\Http\Controllers\Tenant\Admin\Operational\MoveRequestController::class, 'index'])->name('moves.index');
                    Route::put('/moves/{moveRequest}/status', [App\Http\Controllers\Tenant\Admin\Operational\MoveRequestController::class, 'update'])->name('moves.update');
                });

                Route::prefix('ecosystem')->name('ecosystem.')->group(function () {
                    Route::get('/providers', [AdminProviderController::class, 'index'])->name('providers');
                });

                Route::prefix('governance')->name('governance.')->group(function () {
                    Route::resource('pqrs', AdminTicketController::class)
                        ->parameters(['pqrs' => 'ticket'])
                        ->only(['index', 'show']);
                    Route::put('/pqrs/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('pqrs.status');
                    Route::post('/pqrs/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('pqrs.reply');
                });

                Route::prefix('security')->name('security.')->group(function () {
                    Route::get('/radar', [RadarController::class, 'index'])->name('radar.index');
                    Route::get('/radar/search', [RadarController::class, 'search'])->name('radar.search');
                });

                Route::prefix('settings')->name('settings.')->group(function () {
                    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
                    Route::post('/team/invite', [TeamController::class, 'invite'])->name('team.invite');
                    Route::put('/team/{user}', [TeamController::class, 'update'])->name('team.update');
                    Route::delete('/team/{user}', [TeamController::class, 'destroy'])->name('team.destroy');
                });

                Route::prefix('financial')->name('financial.')->group(function () {
                    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
                    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
                });
            });

        Route::prefix('resident')->name('tenant.resident.')->group(function () {
            Route::get('/dashboard', [ResidentDashboardController::class, 'index'])->name('dashboard');
            Route::get('/activity', [ActivityController::class, 'index'])->name('activity');

            Route::prefix('core')->name('core.')->group(function () {
                Route::get('/operations', [OperationController::class, 'index'])->name('operations');
            });

            Route::prefix('logistics')->name('logistics.')->group(function () {
                Route::get('/moves', [MoveRequestController::class, 'index'])->name('moves.index');
                Route::post('/moves', [MoveRequestController::class, 'store'])->name('moves.store');
            });

            Route::prefix('governance')->name('governance.')->group(function () {
                Route::get('/pqrs', [TicketController::class, 'index'])->name('pqrs');
                Route::get('/pqrs/{ticket}', [TicketController::class, 'show'])->name('pqrs.show');
                Route::post('/pqrs/{ticket}/reply', [TicketController::class, 'reply'])->name('pqrs.reply');
                Route::post('/pqrs/{ticket}/reopen', [TicketController::class, 'reopen'])->name('pqrs.reopen');
                Route::post('/pqrs', [TicketController::class, 'store'])->name('pqrs.store');
                Route::put('/pqrs/{ticket}', [TicketController::class, 'update'])->name('pqrs.update');
                Route::delete('/pqrs/{ticket}', [TicketController::class, 'destroy'])->name('pqrs.destroy');

                Route::get('/polls', [PollController::class, 'index'])->name('polls.index');
                Route::post('/polls/{poll}/vote', [SubmitPollVoteAction::class, 'handle'])->name('polls.vote');
            });

            Route::prefix('census')->name('census.')->group(function () {
                Route::get('/', [CensusController::class, 'index'])->name('index');
                Route::post('/family', [CensusController::class, 'storeFamilyMember'])->name('family.store');
                Route::put('/family/{familyMember}', [CensusController::class, 'updateFamilyMember'])->name('family.update');
                Route::delete('/family/{familyMember}', [CensusController::class, 'destroyFamilyMember'])->name('family.destroy');

                Route::post('/vehicles', [CensusController::class, 'storeVehicle'])->name('vehicles.store');
                Route::put('/vehicles/{vehicle}', [CensusController::class, 'updateVehicle'])->name('vehicles.update');
                Route::delete('/vehicles/{vehicle}', [CensusController::class, 'destroyVehicle'])->name('vehicles.destroy');

                Route::post('/pets', [CensusController::class, 'storePet'])->name('pets.store');
                Route::put('/pets/{pet}', [CensusController::class, 'updatePet'])->name('pets.update');
                Route::delete('/pets/{pet}', [CensusController::class, 'destroyPet'])->name('pets.destroy');
            });

            Route::prefix('ecosystem')->name('ecosystem.')->group(function () {
                Route::get('/', [EcosystemController::class, 'index'])->name('index');
                Route::get('/providers', [ResidentProviderController::class, 'index'])->name('providers');
            });

            Route::prefix('access')->name('access.')->group(function () {
                Route::get('/', [AccessController::class, 'index'])->name('index');
                Route::post('/', [AccessController::class, 'store'])->name('store');
                Route::delete('/{sponsoredUser}', [AccessController::class, 'revoke'])->name('revoke');
            });
        });
    });
