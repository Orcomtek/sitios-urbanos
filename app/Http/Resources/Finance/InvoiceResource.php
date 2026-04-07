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
            'type' => $this->type->value ?? $this->type,
            'status' => $this->status->value ?? $this->status,
            'amount' => $this->amount,
            'description' => $this->description,
            'issued_at' => $this->issued_at?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
