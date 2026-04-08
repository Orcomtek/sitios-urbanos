<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\Ecosystem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'category' => $this->category->value ?? $this->category,
            'status' => $this->status->value ?? $this->status,
            'show_contact_info' => $this->show_contact_info,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'resident' => $this->whenLoaded('resident', function () {
                $contactInfo = $this->show_contact_info ? [
                    'email' => $this->resident->email,
                    'phone' => $this->resident->phone,
                ] : [
                    'email' => null,
                    'phone' => null,
                ];

                return [
                    'id' => $this->resident->id,
                    'name' => $this->resident->full_name,
                ] + $contactInfo;
            }),
        ];
    }
}
