<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('communities.index');
})->middleware(['auth']);

Route::middleware('auth')->group(function () {
    Route::get('/comunidades', [CommunityController::class, 'index'])->name('communities.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\Tenant\UnitController;
use App\Http\Middleware\TenantMiddleware;

Route::middleware(['auth', TenantMiddleware::class])
    ->prefix('c/{community_slug}')
    ->group(function () {
        Route::resource('units', UnitController::class);
    });

require __DIR__.'/auth.php';
