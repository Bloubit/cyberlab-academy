<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'module_id' => $this->module_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content_type' => $this->content_type,
            'content' => $this->content,
            'sort_order' => $this->sort_order,
            'is_published' => $this->is_published,
            'is_free' => $this->is_free,
            'duration_minutes' => $this->duration_minutes,
            'xp_reward' => $this->xp_reward,
            'video' => new VideoResource($this->whenLoaded('video')),
            'resources' => LessonResourceResource::collection($this->whenLoaded('resources')),
            'progress' => LessonProgressResource::collection($this->whenLoaded('progress')),
        ];
    }
}
