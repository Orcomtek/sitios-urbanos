<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountStatementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // the array passed will contain the balance and ledger entries
        return [
            'unit_id' => $this['unit_id'],
            'balance' => $this['balance'],
            'currency' => 'COP',
            'summary' => [
                'total_charges' => $this['total_charges'],
                'total_payments' => abs($this['total_payments']),
            ],
            'movements' => LedgerEntryResource::collection($this['movements']),
        ];
    }
}
