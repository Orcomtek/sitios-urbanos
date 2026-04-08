<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityTimelineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'source' => $this['source'],
            'type' => $this['type'],
            'title' => $this['title'],
            'message' => $this['message'],
            'created_at' => $this['created_at'],
            'entity_id' => $this['entity_id'] ?? null,
            'entity_type' => $this['entity_type'] ?? null,
            'metadata' => $this['metadata'] ?? null,
        ];
    }
}
