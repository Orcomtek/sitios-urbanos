<?php

namespace App\Http\Resources\Governance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'votes_count' => $this->whenCounted('votes'),
        ];
    }
}
