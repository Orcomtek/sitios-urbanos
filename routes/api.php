<?php

use App\Http\Controllers\Api\Finance\AccountStatementController;
use App\Http\Controllers\Api\Finance\FinancialStateController;
use App\Http\Controllers\Api\Governance\PqrsController;
use App\Http\Controllers\Webhooks\EpaycoWebhookController;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhooks/epayco', EpaycoWebhookController::class);

$centralDomain = config('app.central_domain', 'sitios-urbanos.test');

Route::domain('{community_slug}.'.$centralDomain)
    ->middleware(['auth:sanctum', TenantMiddleware::class])
    ->group(function () {
        Route::get('/finance/invoices/{invoice}', [FinancialStateController::class, 'invoice'])->name('api.finance.invoice');
        Route::get('/finance/payments/{payment}', [FinancialStateController::class, 'payment'])->name('api.finance.payment');

        Route::get('/finance/units/{unit}/statement', [AccountStatementController::class, 'show'])->name('api.finance.units.statement');
        Route::get('/finance/units/{unit}/invoices', [FinancialStateController::class, 'invoices'])->name('api.finance.units.invoices');
        Route::get('/finance/units/{unit}/payments', [FinancialStateController::class, 'payments'])->name('api.finance.units.payments');

        Route::prefix('governance')->group(function () {
            Route::get('/pqrs', [PqrsController::class, 'index'])->name('api.governance.pqrs.index');
            Route::post('/pqrs', [PqrsController::class, 'store'])->name('api.governance.pqrs.store');
            Route::get('/pqrs/{pqrs}', [PqrsController::class, 'show'])->name('api.governance.pqrs.show');
            Route::patch('/pqrs/{pqrs}/status', [PqrsController::class, 'update_status'])->name('api.governance.pqrs.update_status');
        });
    });
