<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\AccountStatementResource;
use App\Models\LedgerEntry;
use App\Models\Unit;
use App\Services\TenantContext;

class AccountStatementController extends Controller
{
    public function __construct(private TenantContext $tenantContext) {}

    public function show(string $communitySlug, Unit $unit)
    {
        $tenantId = $this->tenantContext->require()->id;

        // Ensure unit belongs to the active tenant
        if ($unit->community_id !== $tenantId) {
            abort(404);
        }

        // Endpoint Security: MUST validate unit belongs to authenticated user (via resident relationship)
        // Assume resident model has a direct user_id. Or user()->residents()->where('unit_id', $unit->id)->exists()
        $user = request()->user();
        $isResident = $unit->residents()->where('user_id', $user->id)->exists();
        // Maybe also consider if the user can be an admin who is viewing this? In SRS, we assume residents view their own statements unless admin. For now, strict resident check. Wait, some communities have admins that view statements. If it's the resident's statement, checking resident relation is safest, or we can check if user is attached to community as admin. Let's stick to user belonging to unit via resident or community logic.
        // Actually, if we just check user belonging to the unit.
        // Let's implement this: "MUST validate: unit belongs to current tenant, unit belongs to authenticated user (via resident relationship)"
        if (! $isResident) {
            abort(403, 'Unauthorized to view statement for this unit.');
        }

        // Calculate statement directly from ledger as single source of truth
        $movements = LedgerEntry::where('unit_id', $unit->id)
            ->where('community_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        $balance = $movements->sum('amount');

        // Let's calculate total charges and total payments
        $totalCharges = $movements->where('amount', '>', 0)->sum('amount');
        $totalPayments = $movements->where('amount', '<', 0)->sum('amount');

        return new AccountStatementResource([
            'unit_id' => $unit->id,
            'balance' => $balance,
            'total_charges' => $totalCharges,
            'total_payments' => $totalPayments,
            'movements' => $movements,
        ]);
    }
}
