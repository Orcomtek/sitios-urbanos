<?php

namespace App\Http\Resources\Security;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyEventResource extends JsonResource
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
            'unit_number' => $this->whenLoaded('unit', fn () => $this->unit->number),
            'triggered_by' => $this->triggered_by,
            'triggerer_name' => $this->whenLoaded('triggerer', fn () => $this->triggerer->name),
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'triggered_at' => $this->triggered_at?->toIso8601String(),
            'acknowledged_at' => $this->acknowledged_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'notes' => $this->notes,
        ];
    }
}
