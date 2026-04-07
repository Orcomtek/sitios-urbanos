<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'method' => $this->method->value ?? $this->method,
            'status' => $this->status->value ?? $this->status,
            'amount' => $this->amount,
            'external_reference' => $this->external_reference,
            'paid_at' => $this->paid_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
