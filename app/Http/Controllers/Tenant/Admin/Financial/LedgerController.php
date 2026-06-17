<?php

namespace App\Http\Controllers\Tenant\Admin\Financial;

use App\Actions\Financial\CalculateUnitBalanceAction;
use App\Actions\Financial\IssueAccountingNoteAction;
use App\Actions\Financial\ProcessManualPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Financial\StoreAccountingNoteRequest;
use App\Http\Requests\Financial\StoreManualPaymentRequest;
use App\Models\BillingConcept;
use App\Models\Unit;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LedgerController extends Controller
{
    public function index(CalculateUnitBalanceAction $calculator): Response
    {
        $units = Unit::with(['invoices' => function($query) {
            $query->where('status', 'pending');
        }, 'payments', 'financialAdjustments'])->get();

        $unitsWithBalance = $units->map(function ($unit) use ($calculator) {
            return [
                'id' => $unit->id,
                'identifier' => $unit->identifier,
                'property_type' => $unit->property_type,
                'net_balance' => $calculator->execute($unit),
                'invoices' => $unit->invoices,
            ];
        });

        $community = app(TenantContext::class)->require();

        $billingConcepts = BillingConcept::where('is_active', true)
            ->where(function ($query) use ($community) {
                $query->where('community_id', $community->id)
                      ->orWhereNull('community_id');
            })
            ->get(['id', 'name', 'type']);

        return Inertia::render('Tenant/Admin/Financial/Ledger/Index', [
            'units' => $unitsWithBalance,
            'billing_concepts' => $billingConcepts,
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
}
