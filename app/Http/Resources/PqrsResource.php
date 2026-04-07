<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PqrsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'community_id' => $this->community_id,
            'type' => $this->type,
            'type_name' => $this->type->label(),
            'status' => $this->status,
            'status_name' => $this->status->label(),
            'subject' => $this->subject,
            'description' => $this->description,
            'is_anonymous' => $this->is_anonymous,
            'admin_response' => $this->admin_response,
            'responded_at' => $this->responded_at,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Explicitly enforce anonymity at the API output layer
        if (! $this->is_anonymous) {
            $data['resident_id'] = $this->resident_id;
            // Optionally load resident data if available
            if ($this->relationLoaded('resident')) {
                $data['resident'] = [
                    'id' => $this->resident->id,
                    'full_name' => $this->resident->full_name,
                    'email' => $this->resident->email,
                ];
            }
        }

        return $data;
    }
}
