<?php

use App\Http\Controllers\Api\Cockpit\DashboardController;
use App\Http\Controllers\Api\Finance\AccountStatementController;
use App\Http\Controllers\Api\Finance\FinancialStateController;
use App\Http\Controllers\Api\Finance\InvoicePaymentController;
use App\Http\Controllers\Api\Governance\AnnouncementController;
use App\Http\Controllers\Api\Governance\DocumentController;
use App\Http\Controllers\Api\Governance\PollController;
use App\Http\Controllers\Api\Governance\PqrsController;
use App\Http\Controllers\Api\Security\AccessInvitationController;
use App\Http\Controllers\Api\Security\EmergencyEventController;
use App\Http\Controllers\Api\Security\PackageController;
use App\Http\Controllers\Api\Security\VisitorController;
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
        Route::get('/cockpit/dashboard', [DashboardController::class, 'index'])->name('api.cockpit.dashboard');

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
            Route::get('/announcements', [AnnouncementController::class, 'index'])->name('api.governance.announcements.index');
            Route::post('/announcements', [AnnouncementController::class, 'store'])->name('api.governance.announcements.store');
            Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('api.governance.announcements.destroy');

            // Documents
            Route::get('/documents', [DocumentController::class, 'index'])->name('api.governance.documents.index');
            Route::post('/documents', [DocumentController::class, 'store'])->name('api.governance.documents.store');
            Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('api.governance.documents.destroy');

            // Polls
            Route::get('/polls', [PollController::class, 'index'])->name('api.governance.polls.index');
            Route::post('/polls', [PollController::class, 'store'])->name('api.governance.polls.store');
            Route::get('/polls/{poll}', [PollController::class, 'show'])->name('api.governance.polls.show');
            Route::patch('/polls/{poll}/close', [PollController::class, 'close'])->name('api.governance.polls.close');
            Route::post('/polls/{poll}/vote', [PollController::class, 'vote'])->name('api.governance.polls.vote');
        });

        Route::prefix('security')->group(function () {
            // Visitors
            Route::get('/visitors', [VisitorController::class, 'index'])->name('api.security.visitors.index');
            Route::post('/visitors', [VisitorController::class, 'store'])->name('api.security.visitors.store');
            Route::get('/visitors/{visitor}', [VisitorController::class, 'show'])->name('api.security.visitors.show');
            Route::patch('/visitors/{visitor}/enter', [VisitorController::class, 'enter'])->name('api.security.visitors.enter');
            Route::patch('/visitors/{visitor}/exit', [VisitorController::class, 'exit'])->name('api.security.visitors.exit');

            // Packages
            Route::get('/packages', [PackageController::class, 'index'])->name('api.security.packages.index');
            Route::post('/packages', [PackageController::class, 'store'])->name('api.security.packages.store');
            Route::get('/packages/{package}', [PackageController::class, 'show'])->name('api.security.packages.show');
            Route::patch('/packages/{package}/deliver', [PackageController::class, 'deliver'])->name('api.security.packages.deliver');
            Route::patch('/packages/{package}/return', [PackageController::class, 'return'])->name('api.security.packages.return');

            // Emergencies
            Route::get('/emergencies', [EmergencyEventController::class, 'index'])->name('api.security.emergencies.index');
            Route::post('/emergencies', [EmergencyEventController::class, 'store'])->name('api.security.emergencies.store');
            Route::get('/emergencies/{emergency}', [EmergencyEventController::class, 'show'])->name('api.security.emergencies.show');
            Route::patch('/emergencies/{emergency}/ack', [EmergencyEventController::class, 'acknowledge'])->name('api.security.emergencies.ack');
            Route::patch('/emergencies/{emergency}/resolve', [EmergencyEventController::class, 'resolve'])->name('api.security.emergencies.resolve');

            // Access Invitations
            Route::get('/invitations', [AccessInvitationController::class, 'index'])->name('api.security.invitations.index');
            Route::post('/invitations', [AccessInvitationController::class, 'store'])->name('api.security.invitations.store');
            Route::get('/invitations/{invitation}', [AccessInvitationController::class, 'show'])->name('api.security.invitations.show');
            Route::patch('/invitations/{invitation}/revoke', [AccessInvitationController::class, 'revoke'])->name('api.security.invitations.revoke');
            Route::patch('/invitations/{invitation}/consume', [AccessInvitationController::class, 'consume'])->name('api.security.invitations.consume');
        });
    });
