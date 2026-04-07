<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\InvoiceResource;
use App\Http\Resources\Finance\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Unit;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;

class FinancialStateController extends Controller
{
    /**
     * Get the state of an invoice.
     */
    public function invoice(string $community_slug, string $invoice): JsonResponse
    {
        $invoiceModel = Invoice::findOrFail($invoice);

        return response()->json([
            'id' => $invoiceModel->id,
            'status' => $invoiceModel->status->value ?? $invoiceModel->status,
            'amount' => $invoiceModel->amount,
            'due_date' => $invoiceModel->due_date ? $invoiceModel->due_date->toDateString() : null,
        ]);
    }

    /**
     * Get the state of a payment.
     */
    public function payment(string $community_slug, string $payment): JsonResponse
    {
        $paymentModel = Payment::findOrFail($payment);

        return response()->json([
            'id' => $paymentModel->id,
            'status' => $paymentModel->status->value ?? $paymentModel->status,
            'amount' => $paymentModel->amount,
            'invoice_id' => $paymentModel->invoice_id,
            'paid_at' => $paymentModel->paid_at ? $paymentModel->paid_at->toDateTimeString() : null,
        ]);
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
