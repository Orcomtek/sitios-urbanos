<?php

use App\Http\Controllers\Api\Finance\FinancialStateController;
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
    });
