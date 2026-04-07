<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LedgerEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value ?? $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
