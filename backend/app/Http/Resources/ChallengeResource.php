<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lab_id' => $this->lab_id,
            'title' => $this->title,
            'description' => $this->description,
            'hint' => $this->hint,
            'points' => $this->points,
            'sort_order' => $this->sort_order,
            'metadata' => $this->metadata,
            'flags' => $this->when($request->user()?->isAdmin(), $this->flags),
        ];
    }
}
