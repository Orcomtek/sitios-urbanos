<?php

use App\Http\Controllers\Webhook\EpaycoWebhookController;
use Illuminate\Support\Facades\Route;

$centralDomain = config('app.central_domain');

Route::domain($centralDomain)->group(function () {
    Route::post('/webhooks/epayco', [EpaycoWebhookController::class, 'handle'])
        ->name('webhooks.epayco');
});
