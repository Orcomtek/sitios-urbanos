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
                $q->with('payments')->orderBy('created_at', 'desc');
            },
            'payments' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'financialAdjustments' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
        ])->findOrFail($unitId);

        // Ensure tenant isolation
        if ($unit->community_id !== $community->id) {
            abort(404);
        }

        // Dynamically update display status
        foreach ($unit->invoices as $invoice) {
            $paidAmount = $invoice->payments ? $invoice->payments->sum('amount') : 0;
            if ($paidAmount >= $invoice->total) {
                $invoice->status = 'paid';
            }
        }

        $netBalance = $calculateBalanceAction->execute($unit);
        $unit->net_balance = $netBalance;

        return Inertia::render('Tenant/Resident/Financial/Statement/Index', [
            'unit' => $unit,
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
            'Content-Disposition' => 'attachment; filename="Factura-' . $invoice->id . '.pdf"',
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
                'Content-Disposition' => 'attachment; filename="PazYSalvo-' . ($unit->identifier ?? $unit->id) . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
