<?php

namespace App\Actions\Dashboard;

use App\Enums\CommunityRole;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\PqrsStatus;
use App\Models\EmergencyEvent;
use App\Models\Governance\Poll;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Pqrs;
use App\Models\Visitor;

class GetOperationalDashboardAction
{
    /**
     * Get aggregated operational dashboard data based on role visibility.
     */
    public function execute(CommunityRole $role): array
    {
        $dashboard = [
            'role' => $role->value,
            'generated_at' => now()->toIso8601String(),
            'widgets' => [
                'emergencies' => $this->getEmergenciesWidget(),
                'visitors' => $this->getVisitorsWidget(),
                'packages' => $this->getPackagesWidget(),
            ],
        ];

        if ($role === CommunityRole::Admin) {
            $dashboard['widgets']['pqrs'] = $this->getPqrsWidget();
            $dashboard['widgets']['polls'] = $this->getPollsWidget();
            $dashboard['widgets']['finance'] = $this->getFinanceWidget();
        }

        return $dashboard;
    }

    private function getEmergenciesWidget(): array
    {
        return [
            'active_count' => EmergencyEvent::where('status', 'active')->count(),
            'recent_active' => EmergencyEvent::where('status', 'active')
                ->with('unit')
                ->latest('triggered_at')
                ->limit(5)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'type' => $e->type,
                    'triggered_at' => $e->triggered_at?->toIso8601String(),
                    'unit' => $e->unit ? ['id' => $e->unit->id, 'number' => $e->unit->number] : null,
                ]),
        ];
    }

    private function getVisitorsWidget(): array
    {
        return [
            'pending_count' => Visitor::where('status', 'pending')->count(),
            'entered_count' => Visitor::where('status', 'entered')->count(),
        ];
    }

    private function getPackagesWidget(): array
    {
        return [
            'pending_pickup_count' => Package::where('status', 'received')->count(),
            'recent_pending' => Package::where('status', 'received')
                ->with('unit')
                ->latest('received_at')
                ->limit(5)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'recipient_name' => $p->recipient_name,
                    'received_at' => $p->received_at?->toIso8601String(),
                    'unit' => $p->unit ? ['id' => $p->unit->id, 'number' => $p->unit->number] : null,
                ]),
        ];
    }

    private function getPqrsWidget(): array
    {
        return [
            'open_count' => Pqrs::whereIn('status', [PqrsStatus::OPEN, PqrsStatus::IN_PROGRESS])->count(),
        ];
    }

    private function getPollsWidget(): array
    {
        // Polls has custom logic `isOpen()` but for queries we can just check status and dates directly.
        return [
            'active_count' => Poll::where('status', 'open')
                ->where(function ($query) {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->count(),
        ];
    }

    private function getFinanceWidget(): array
    {
        $pendingInvoices = Invoice::where('status', InvoiceStatus::PENDING);

        return [
            'pending_invoices_count' => $pendingInvoices->count(),
            'pending_amount' => (int) $pendingInvoices->sum('amount'),
            'recent_confirmed_payments_count' => Payment::where('status', PaymentStatus::CONFIRMED)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
        ];
    }
}
