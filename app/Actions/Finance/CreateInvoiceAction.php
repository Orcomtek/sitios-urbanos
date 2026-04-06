<?php

namespace App\Actions\Finance;

use App\Models\Invoice;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use App\Enums\InvoiceStatus;
use App\Enums\LedgerEntryType;
use App\Services\TenantContext;

class CreateInvoiceAction
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    public function execute(array $data): Invoice
    {
        $communityId = $this->tenantContext->require()->id;

        return DB::transaction(function () use ($data, $communityId) {
            $invoice = Invoice::create([
                'community_id' => $communityId,
                'unit_id' => $data['unit_id'],
                'resident_id' => $data['resident_id'] ?? null,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'issued_at' => $data['issued_at'],
                'due_date' => $data['due_date'],
                'description' => $data['description'] ?? null,
                'status' => InvoiceStatus::PENDING,
            ]);

            LedgerEntry::create([
                'community_id' => $communityId,
                'unit_id' => $data['unit_id'],
                'invoice_id' => $invoice->id,
                'type' => LedgerEntryType::CHARGE,
                'amount' => $data['amount'],
                'description' => 'Charge for invoice: ' . $invoice->id,
            ]);

            return $invoice;
        });
    }
}
