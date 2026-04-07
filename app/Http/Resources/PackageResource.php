<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
            'unit_id' => $this->unit_id,
            'unit' => new UnitResource($this->whenLoaded('unit')),

            'carrier' => $this->carrier,
            'tracking_number' => $this->tracking_number,
            'sender_name' => $this->sender_name,
            'recipient_name' => $this->recipient_name,
            'description' => $this->description,

            'status' => $this->status,
            'received_at' => $this->received_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'returned_at' => $this->returned_at?->toIso8601String(),

            'notes' => $this->notes,

            'received_by' => new UserResource($this->whenLoaded('receiver')),
            'delivered_by' => new UserResource($this->whenLoaded('deliverer')),

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
