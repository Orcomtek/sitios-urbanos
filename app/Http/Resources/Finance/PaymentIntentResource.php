<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentIntentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'status' => $this->status->value ?? $this->status,
            'amount' => $this->amount,
            'platform_commission' => $this->platform_commission,
            'net_amount' => $this->net_amount,
            'gateway' => [
                'provider' => 'epayco', // In the future, this might be dynamic based on method
                'flow' => 'split',
            ],
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
