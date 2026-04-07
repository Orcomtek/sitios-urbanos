<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorResource extends JsonResource
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
            'community_id' => $this->community_id,
            'unit_id' => $this->unit_id,
            'unit' => [
                'id' => $this->unit?->id,
                'identifier' => $this->unit?->identifier,
            ],
            'created_by' => $this->created_by,
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'email' => $this->creator?->email,
            ],
            'name' => $this->name,
            'document_number' => $this->document_number,
            'type' => $this->type,
            'status' => $this->status,
            'expected_at' => $this->expected_at?->toIso8601String(),
            'entered_at' => $this->entered_at?->toIso8601String(),
            'exited_at' => $this->exited_at?->toIso8601String(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
