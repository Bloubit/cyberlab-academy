<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'preview_video_url' => $this->preview_video_url,
            'difficulty_level' => $this->difficulty_level,
            'estimated_hours' => $this->estimated_hours,
            'price' => $this->price,
            'currency' => $this->currency,
            'is_free' => $this->is_free,
            'is_published' => $this->is_published,
            'is_featured' => $this->is_featured,
            'requirements' => $this->requirements,
            'learning_outcomes' => $this->learning_outcomes,
            'tags' => $this->tags,
            'stats' => $this->stats,
            'enrollment_count' => $this->enrollment_count,
            'completion_rate' => $this->completion_rate,
            'total_lessons' => $this->total_lessons,
            'total_duration' => $this->total_duration,
            'category' => new CourseCategoryResource($this->whenLoaded('category')),
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
            'labs' => LabResource::collection($this->whenLoaded('labs')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
