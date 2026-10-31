<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'difficulty_level' => $this->difficulty_level,
            'type' => $this->type,
            'duration_minutes' => $this->duration_minutes,
            'xp_reward' => $this->xp_reward,
            'is_published' => $this->is_published,
            'learning_objectives' => $this->learning_objectives,
            'hints' => $this->hints,
            'network_topology' => $this->network_topology,
            'settings' => $this->settings,
            'machines' => MachineResource::collection($this->whenLoaded('machines')),
            'challenges' => ChallengeResource::collection($this->whenLoaded('challenges')),
            'course' => new CourseResource($this->whenLoaded('course')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
