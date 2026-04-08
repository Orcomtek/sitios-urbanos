<?php

namespace App\Actions\Cockpit;

use App\Enums\CommunityRole;
use App\Models\AccessInvitation;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Pqrs;
use App\Models\Resident;
use App\Models\Visitor;

class GetResidentCockpitAction
{
    /**
     * Get aggregated data for the resident cockpit.
     */
    public function execute(int $userId, CommunityRole $role): array
    {
        $limit = 5;

        // Get resident's active units within the current tenant (automatically scoped)
        $residentRecords = Resident::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $unitIds = $residentRecords->pluck('unit_id')->toArray();

        if (empty($unitIds)) {
            return $this->emptyResponse($role);
        }

        // 1. Finance Widget
        $pendingInvoices = Invoice::whereIn('unit_id', $unitIds)
            ->where('status', 'pending')
            ->oldest()
            ->get();

        $finance = [
            'pending_count' => $pendingInvoices->count(),
            'pending_amount' => $pendingInvoices->sum('amount'),
            'recent_invoices' => $pendingInvoices->take($limit)->map(fn ($invoice) => [
                'id' => $invoice->id,
                'description' => $invoice->description,
                'amount' => $invoice->amount,
                'due_date' => $invoice->due_date?->toIso8601String(),
                'status' => $invoice->status,
                'unit' => $invoice->unit ? ['id' => $invoice->unit->id, 'unit_number' => $invoice->unit->unit_number] : null,
            ])->toArray(),
        ];

        // 2. Packages Widget
        $packages = Package::whereIn('unit_id', $unitIds)
            ->where('status', 'received') // Meaning pending pickup
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($package) => [
                'id' => $package->id,
                'carrier' => $package->carrier,
                'recipient_name' => $package->recipient_name,
                'status' => $package->status,
                'received_at' => $package->created_at->toIso8601String(),
                'unit' => $package->unit ? ['id' => $package->unit->id, 'unit_number' => $package->unit->unit_number] : null,
            ])->toArray();

        // 3. Invitations Widget
        $invitations = AccessInvitation::whereIn('unit_id', $unitIds)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($invitation) => [
                'id' => $invitation->id,
                'guest_name' => $invitation->guest_name,
                'code' => $invitation->code,
                'status' => $invitation->status,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'unit' => $invitation->unit ? ['id' => $invitation->unit->id, 'unit_number' => $invitation->unit->unit_number] : null,
            ])->toArray();

        // 4. PQRS Widget
        $residentIds = $residentRecords->pluck('id')->toArray();
        $pqrsList = Pqrs::whereIn('resident_id', $residentIds)
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($pqrs) => [
                'id' => $pqrs->id,
                'subject' => $pqrs->subject,
                'status' => $pqrs->status,
                'created_at' => $pqrs->created_at->toIso8601String(),
            ])->toArray();

        // 5. Visitors Widget
        $visitors = Visitor::whereIn('unit_id', $unitIds)
            ->whereIn('status', ['pending', 'entered'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($visitor) => [
                'id' => $visitor->id,
                'name' => $visitor->name,
                'status' => $visitor->status,
                'created_at' => $visitor->created_at->toIso8601String(),
                'unit' => $visitor->unit ? ['id' => $visitor->unit->id, 'unit_number' => $visitor->unit->unit_number] : null,
            ])->toArray();

        return [
            'generated_at' => now()->toIso8601String(),
            'role' => $role->value,
            'finance' => $finance,
            'packages' => $packages,
            'invitations' => $invitations,
            'pqrs' => $pqrsList,
            'visitors' => $visitors,
        ];
    }

    protected function emptyResponse(CommunityRole $role): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'role' => $role->value,
            'finance' => ['pending_count' => 0, 'pending_amount' => 0, 'recent_invoices' => []],
            'packages' => [],
            'invitations' => [],
            'pqrs' => [],
            'visitors' => [],
        ];
    }
}
