<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\Global\AcceptInvitationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

$centralDomain = config('app.central_domain');

Route::domain($centralDomain)->group(function () {
    Route::get('/', function () {
        return redirect()->route('communities.index');
    })->middleware(['auth']);

    Route::get('/invitations/accept', [AcceptInvitationController::class, 'show'])->name('invitations.accept.show');
    Route::post('/invitations/accept', [AcceptInvitationController::class, 'store'])->name('invitations.accept.store');

    Route::middleware('auth')->group(function () {
        Route::get('/comunidades', [CommunityController::class, 'index'])->name('communities.index');
        Route::get('/comunidades/{slug}/ingresar', [CommunityController::class, 'enter'])->name('communities.enter');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});
