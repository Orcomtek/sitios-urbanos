<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
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
}
