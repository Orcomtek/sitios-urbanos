<?php

namespace App\Http\Controllers\Tenant\Resident\Financial;

use App\Actions\Financial\CalculateUnitBalanceAction;
use App\Http\Controllers\Controller;
use App\Models\Financial\Invoice;
use App\Models\Unit;
use App\Services\Financial\BrowsershotPdfService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatementController extends Controller
{
    public function index(string $community_slug, Request $request, CalculateUnitBalanceAction $calculateBalanceAction)
    {
        $user = $request->user();
        $community = app(TenantContext::class)->require();

        $communityUser = $user->communities()->where('communities.id', $community->id)->first();
        if (! $communityUser || ! $communityUser->pivot->unit_id) {
            abort(403, 'No unit associated with your resident profile in this community.');
        }

        $unitId = $communityUser->pivot->unit_id;

        $unit = Unit::with([
            'invoices' => function ($q) {
                $q->with(['payments' => fn ($p) => $p->where('status', 'confirmed')])->orderBy('created_at', 'desc');
            },
            'payments' => function ($q) {
                $q->with('invoice:id,invoice_number,billing_period')->orderBy('created_at', 'desc');
            },
            'financialAdjustments' => function ($q) {
                $q->with(['billingConcept:id,name', 'invoice:id,invoice_number,billing_period'])->orderBy('created_at', 'desc');
            },
        ])->findOrFail($unitId);

        // Ensure tenant isolation
        if ($unit->community_id !== $community->id) {
            abort(404);
        }

        // Calculate pending_amount per invoice using the authoritative formula
        foreach ($unit->invoices as $invoice) {
            $confirmedPayments = $invoice->payments ? $invoice->payments->sum('amount') : 0;

            $invoiceAdjustments = $unit->financialAdjustments->where('invoice_id', $invoice->id);
            $creditAdjustments = $invoiceAdjustments->where('type', 'credit')->sum('amount');
            $debitAdjustments = $invoiceAdjustments->where('type', 'debit')->sum('amount');

            $pendingAmount = max(0, $invoice->total - $confirmedPayments - $creditAdjustments + $debitAdjustments);

            $invoice->pending_amount = $pendingAmount;

            if ($pendingAmount <= 0) {
                $invoice->status = 'paid';
            }

            $invoice->created_at_formatted = $invoice->created_at ? $invoice->created_at->timezone(config('app.timezone'))->format('d/m/Y') : null;
        }

        $unit->payments->transform(function ($payment) {
            $payment->created_at_formatted = $payment->created_at ? $payment->created_at->timezone(config('app.timezone'))->format('d/m/Y') : null;

            return $payment;
        });

        $unit->financialAdjustments->transform(function ($adj) {
            $adj->created_at_formatted = $adj->created_at ? $adj->created_at->timezone(config('app.timezone'))->format('d/m/Y') : null;

            return $adj;
        });

        $netBalance = $calculateBalanceAction->execute($unit);
        $unit->net_balance = $netBalance;

        return Inertia::render('Tenant/Resident/Financial/Statement/Index', [
            'unit' => $unit,
            'epaycoConfig' => [
                'public_key' => config('epayco.public_key'),
                'testing' => config('epayco.testing'),
            ],
        ]);
    }

    public function downloadInvoice(string $community_slug, Invoice $invoice, BrowsershotPdfService $pdfService)
    {
        $community = app(TenantContext::class)->require();
        if ($invoice->community_id !== $community->id) {
            abort(404);
        }

        $pdfBinary = $pdfService->generateInvoicePdf($invoice);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Factura-'.$invoice->id.'.pdf"',
        ]);
    }

    public function downloadPazYSalvo(string $community_slug, Request $request, BrowsershotPdfService $pdfService)
    {
        $user = $request->user();
        $community = app(TenantContext::class)->require();

        $communityUser = $user->communities()->where('communities.id', $community->id)->first();
        if (! $communityUser || ! $communityUser->pivot->unit_id) {
            abort(403, 'No unit associated with your resident profile in this community.');
        }

        $unit = Unit::findOrFail($communityUser->pivot->unit_id);
        if ($unit->community_id !== $community->id) {
            abort(404);
        }

        try {
            $pdfBinary = $pdfService->generatePazYSalvoPdf($unit);

            return response($pdfBinary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="PazYSalvo-'.($unit->identifier ?? $unit->id).'.pdf"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
