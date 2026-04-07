<?php

namespace App\Http\Controllers\Api\Finance;

use App\Actions\Finance\CreatePaymentAttemptAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\PaymentIntentResource;
use App\Models\Invoice;
use App\Services\TenantContext;

class InvoicePaymentController extends Controller
{
    public function store(string $community_slug, string $invoice, CreatePaymentAttemptAction $action)
    {
        $invoiceModel = Invoice::with('unit')->findOrFail($invoice);
        
        $tenantId = app(TenantContext::class)->require()->id;

        // Enforce tenant boundary
        if ($invoiceModel->community_id !== $tenantId) {
            abort(404);
        }

        // Validate user access to invoice unit
        $user = request()->user();
        if (! $invoiceModel->unit->residents()->where('user_id', $user->id)->exists()) {
             abort(403, 'Unauthorized. Only residents can pay this invoice.');
        }

        try {
            $payment = $action->execute($invoiceModel);
            return new PaymentIntentResource($payment);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
