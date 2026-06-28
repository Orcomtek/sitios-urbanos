<?php

namespace App\Http\Controllers\Tenant\Admin\Financial;

use App\Actions\Financial\CalculateUnitBalanceAction;
use App\Actions\Financial\IssueAccountingNoteAction;
use App\Actions\Financial\ProcessManualPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Financial\StoreAccountingNoteRequest;
use App\Http\Requests\Financial\StoreManualPaymentRequest;
use App\Models\BillingConcept;
use App\Models\Financial\Invoice;
use App\Models\Unit;
use App\Services\Financial\BrowsershotPdfService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LedgerController extends Controller
{
    public function index(CalculateUnitBalanceAction $calculator, Request $request): Response
    {
        $community = app(TenantContext::class)->require();

        $periods = Invoice::where('community_id', $community->id)
            ->select('billing_period')
            ->distinct()
            ->orderBy('billing_period', 'desc')
            ->pluck('billing_period');

        $period = $request->input('period');

        $units = Unit::with(['invoices' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'payments.invoice', 'financialAdjustments.invoice'])->get();

        $unitsWithBalance = $units->map(function ($unit) use ($calculator) {
            return [
                'id' => $unit->id,
                'identifier' => $unit->identifier,
                'property_type' => $unit->property_type,
                'net_balance' => $calculator->execute($unit),
                'invoices' => $unit->invoices->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'billing_period' => $invoice->billing_period,
                        'invoice_number' => $invoice->invoice_number,
                        'total' => $invoice->total,
                        'amount' => $invoice->total,
                        'status' => $invoice->status,
                    ];
                }),
                'payments' => $unit->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'method' => $payment->method,
                        'external_reference' => $payment->external_reference,
                        'notes' => $payment->notes,
                        'created_at' => $payment->created_at ? $payment->created_at->timezone(config('app.timezone'))->format('d/m/Y') : null,
                        'invoice' => $payment->invoice ? [
                            'id' => $payment->invoice->id,
                            'invoice_number' => $payment->invoice->invoice_number,
                            'billing_period' => $payment->invoice->billing_period,
                        ] : null,
                    ];
                }),
                'financial_adjustments' => $unit->financialAdjustments->map(function ($adj) {
                    return [
                        'id' => $adj->id,
                        'amount' => $adj->amount,
                        'type' => $adj->type,
                        'description' => $adj->description,
                        'created_at' => $adj->created_at ? $adj->created_at->timezone(config('app.timezone'))->format('d/m/Y') : null,
                        'invoice' => $adj->invoice ? [
                            'id' => $adj->invoice->id,
                            'invoice_number' => $adj->invoice->invoice_number,
                            'billing_period' => $adj->invoice->billing_period,
                        ] : null,
                    ];
                }),
            ];
        });

        $billingConcepts = BillingConcept::where('is_active', true)
            ->where(function ($query) use ($community) {
                $query->where('community_id', $community->id)
                    ->orWhereNull('community_id');
            })
            ->get(['id', 'name', 'type']);

        return Inertia::render('Tenant/Admin/Financial/Ledger/Index', [
            'units' => $unitsWithBalance,
            'billing_concepts' => $billingConcepts,
            'periods' => $periods,
            'filters' => $request->only(['period']),
        ]);
    }

    public function storePayment(StoreManualPaymentRequest $request, string $community_slug, Unit $unit, ProcessManualPaymentAction $action): RedirectResponse
    {
        $action->execute($unit, $request->validated(), auth()->id());

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function storeAdjustment(StoreAccountingNoteRequest $request, string $community_slug, Unit $unit, IssueAccountingNoteAction $action): RedirectResponse
    {
        $action->execute($unit, $request->validated(), auth()->id());

        return back()->with('success', 'Nota contable registrada correctamente.');
    }

    public function downloadStatement(string $community_slug, Unit $unit, CalculateUnitBalanceAction $calculator, BrowsershotPdfService $pdfService)
    {
        $community = app(TenantContext::class)->require();

        if ($unit->community_id !== $community->id) {
            abort(404);
        }

        $unit->load([
            'invoices' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'payments.invoice',
            'financialAdjustments.invoice',
        ]);

        $netBalance = $calculator->execute($unit);

        $pdfBinary = $pdfService->generateStatementPdf($unit, $netBalance);

        $fileName = 'Estado_de_Cuenta_'.Str::slug($unit->identifier).'_'.now()->timezone(config('app.timezone'))->format('Y-m-d').'.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
