<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status->value ?? $this->status,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'billing_period' => $this->billing_period,
            'issue_date' => $this->issue_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
