<?php

namespace App\Http\Resources\Ecosystem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderServiceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'status' => $this->status->value ?? $this->status,
            'urgency' => $this->urgency->value ?? $this->urgency,
            'scheduled_date' => $this->scheduled_date?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'provider' => [
                'id' => $this->whenLoaded('provider', fn () => $this->provider->id),
                'name' => $this->whenLoaded('provider', fn () => $this->provider->name),
                'category' => $this->whenLoaded('provider', fn () => $this->provider->category),
            ],
            'resident' => [
                'id' => $this->whenLoaded('resident', fn () => $this->resident->id),
                'full_name' => $this->whenLoaded('resident', fn () => $this->resident->full_name),
            ],
        ];
    }
}
