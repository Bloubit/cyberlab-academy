<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstanceResource extends JsonResource
{
    /**
     * Transform the LabInstance into an API response.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'lab_id' => $this->lab_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'container_id' => $this->container_id,
            'connection_info' => $this->connection_info,
            'resources' => $this->resources,
            'started_at' => $this->started_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'stopped_at' => $this->stopped_at?->toISOString(),
            'is_expired' => $this->isExpired(),
            'time_remaining_minutes' => $this->expires_at
                ? max(0, now()->diffInMinutes($this->expires_at, false))
                : null,
            'lab' => new LabResource($this->whenLoaded('lab')),
            'solved_challenges' => $this->when($user, function () use ($user) {
                return $this->lab?->challenges()
                    ->whereHas('submissions', fn($q) => $q
                        ->where('user_id', $user->id)
                        ->where('is_correct', true)
                    )
                    ->pluck('challenges.id')
                    ->toArray() ?? [];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
