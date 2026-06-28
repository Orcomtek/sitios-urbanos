<?php

namespace App\Actions\Finance;

use App\Enums\InvoiceStatus;
use App\Enums\LedgerEntryType;
use App\Models\Financial\Invoice;
use App\Models\LedgerEntry;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateInvoiceAction
{
    public function __construct(private TenantContext $tenantContext) {}

    public function execute(array $data): Invoice
    {
        $communityId = $this->tenantContext->require()->id;

        return DB::transaction(function () use ($data, $communityId) {
            $invoice = Invoice::create([
                'community_id' => $communityId,
                'unit_id' => $data['unit_id'],
                'user_id' => $data['user_id'] ?? null,
                'invoice_number' => $data['invoice_number'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'subtotal' => $data['subtotal'],
                'total' => $data['total'],
                'billing_period' => $data['billing_period'],
                'status' => InvoiceStatus::PENDING,
            ]);

            LedgerEntry::create([
                'community_id' => $communityId,
                'unit_id' => $data['unit_id'],
                'invoice_id' => $invoice->id,
                'type' => LedgerEntryType::CHARGE,
                'amount' => $data['total'],
                'description' => 'Charge for invoice: '.$invoice->id,
            ]);

            return $invoice;
        });
    }
}
