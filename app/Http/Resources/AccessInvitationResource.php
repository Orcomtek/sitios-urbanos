<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessInvitationResource extends JsonResource
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
            'visitor_id' => $this->visitor_id,
            'code' => $this->code,
            'type' => $this->type,
            'status' => $this->status,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'used_at' => $this->used_at?->toIso8601String(),
            'revoked_at' => $this->revoked_at?->toIso8601String(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),

            // Output relationships gently if loaded
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'visitor' => new VisitorResource($this->whenLoaded('visitor')),
        ];
    }
}
