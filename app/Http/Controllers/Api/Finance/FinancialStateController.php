<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\InvoiceResource;
use App\Http\Resources\Finance\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Unit;
use App\Services\TenantContext;

class FinancialStateController extends Controller
{
    /**
     * Get the state of an invoice.
     */
    public function invoice(string $community_slug, string $invoice)
    {
        $invoiceModel = Invoice::with('unit')->findOrFail($invoice);

        $tenantId = app(TenantContext::class)->require()->id;

        if ($invoiceModel->community_id !== $tenantId) {
            abort(404);
        }

        $user = request()->user();
        if (! $invoiceModel->unit->residents()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        return new InvoiceResource($invoiceModel);
    }

    /**
     * Get the state of a payment.
     */
    public function payment(string $community_slug, string $payment)
    {
        $paymentModel = Payment::with('unit')->findOrFail($payment);

        $tenantId = app(TenantContext::class)->require()->id;

        if ($paymentModel->community_id !== $tenantId) {
            abort(404);
        }

        $user = request()->user();
        if (! $paymentModel->unit->residents()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        return new PaymentResource($paymentModel);
    }

    public function invoices(string $community_slug, Unit $unit)
    {
        $tenantId = app(TenantContext::class)->require()->id;

        if ($unit->community_id !== $tenantId) {
            abort(404);
        }

        $user = request()->user();
        if (! $unit->residents()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $invoices = Invoice::where('unit_id', $unit->id)
            ->where('community_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        return InvoiceResource::collection($invoices);
    }

    public function payments(string $community_slug, Unit $unit)
    {
        $tenantId = app(TenantContext::class)->require()->id;

        if ($unit->community_id !== $tenantId) {
            abort(404);
        }

        $user = request()->user();
        if (! $unit->residents()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $payments = Payment::where('unit_id', $unit->id)
            ->where('community_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        return PaymentResource::collection($payments);
    }
}
