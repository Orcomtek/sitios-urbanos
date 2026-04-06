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
        Route::resource('units', UnitController::class);
        Route::resource('residents', ResidentController::class);
    });
