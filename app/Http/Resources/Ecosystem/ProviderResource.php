<?php

namespace App\Http\Resources\Ecosystem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->value,
            'contact_details' => $this->contact_details,
            'status' => $this->status->value,
            'is_recommended' => $this->is_recommended,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
