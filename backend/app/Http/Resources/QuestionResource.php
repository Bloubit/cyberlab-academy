<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'question_type' => $this->question_type,
            'question_text' => $this->question_text,
            'question_html' => $this->question_html,
            'code_snippet' => $this->code_snippet,
            'code_language' => $this->code_language,
            'explanation' => $this->explanation,
            'points' => $this->points,
            'sort_order' => $this->sort_order,
            'difficulty' => $this->difficulty,
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}
