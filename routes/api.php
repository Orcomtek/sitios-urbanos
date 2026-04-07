<?php

use App\Http\Controllers\Api\Finance\AccountStatementController;
use App\Http\Controllers\Api\Finance\FinancialStateController;
use App\Http\Controllers\Api\Finance\InvoicePaymentController;
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
        
        Route::post('/finance/invoices/{invoice}/pay', [InvoicePaymentController::class, 'store'])->name('api.finance.invoices.pay');

        Route::get('/finance/units/{unit}/statement', [AccountStatementController::class, 'show'])->name('api.finance.units.statement');
        Route::get('/finance/units/{unit}/invoices', [FinancialStateController::class, 'invoices'])->name('api.finance.units.invoices');
        Route::get('/finance/units/{unit}/payments', [FinancialStateController::class, 'payments'])->name('api.finance.units.payments');

        Route::prefix('governance')->group(function () {
            Route::get('/pqrs', [PqrsController::class, 'index'])->name('api.governance.pqrs.index');
            Route::post('/pqrs', [PqrsController::class, 'store'])->name('api.governance.pqrs.store');
            Route::get('/pqrs/{pqrs}', [PqrsController::class, 'show'])->name('api.governance.pqrs.show');
            Route::patch('/pqrs/{pqrs}/status', [PqrsController::class, 'update_status'])->name('api.governance.pqrs.update_status');
            
            // Announcements
            Route::get('/announcements', [\App\Http\Controllers\Api\Governance\AnnouncementController::class, 'index'])->name('api.governance.announcements.index');
            Route::post('/announcements', [\App\Http\Controllers\Api\Governance\AnnouncementController::class, 'store'])->name('api.governance.announcements.store');
            Route::delete('/announcements/{announcement}', [\App\Http\Controllers\Api\Governance\AnnouncementController::class, 'destroy'])->name('api.governance.announcements.destroy');

            // Documents
            Route::get('/documents', [\App\Http\Controllers\Api\Governance\DocumentController::class, 'index'])->name('api.governance.documents.index');
            Route::post('/documents', [\App\Http\Controllers\Api\Governance\DocumentController::class, 'store'])->name('api.governance.documents.store');
            Route::delete('/documents/{document}', [\App\Http\Controllers\Api\Governance\DocumentController::class, 'destroy'])->name('api.governance.documents.destroy');

            // Polls
            Route::get('/polls', [\App\Http\Controllers\Api\Governance\PollController::class, 'index'])->name('api.governance.polls.index');
            Route::post('/polls', [\App\Http\Controllers\Api\Governance\PollController::class, 'store'])->name('api.governance.polls.store');
            Route::get('/polls/{poll}', [\App\Http\Controllers\Api\Governance\PollController::class, 'show'])->name('api.governance.polls.show');
            Route::patch('/polls/{poll}/close', [\App\Http\Controllers\Api\Governance\PollController::class, 'close'])->name('api.governance.polls.close');
            Route::post('/polls/{poll}/vote', [\App\Http\Controllers\Api\Governance\PollController::class, 'vote'])->name('api.governance.polls.vote');
        });
    });
