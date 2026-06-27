<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentIntentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $splitData = $this->additional['split'] ?? null;

        $gateway = [
            'provider' => 'epayco',
            'flow' => 'split',
        ];

        if ($splitData) {
            $gateway['split'] = [
                'splitpayment' => 'true',
                'split_app_id' => $splitData['split_receiver_id'],
                'split_merchant_id' => $splitData['split_receiver_id'],
                'split_type' => '02',
                'split_primary_receiver' => $splitData['split_receiver_id'],
                'split_primary_receiver_fee' => (string) $splitData['split_percentage'],
            ];
        }

        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'status' => $this->status->value ?? $this->status,
            'amount' => $this->amount,
            'platform_commission' => $this->platform_commission,
            'net_amount' => $this->net_amount,
            'gateway' => $gateway,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
