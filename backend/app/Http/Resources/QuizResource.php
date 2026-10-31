<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
            'title' => $this->title,
            'description' => $this->description,
            'quiz_type' => $this->quiz_type,
            'time_limit_minutes' => $this->time_limit_minutes,
            'passing_score' => $this->passing_score,
            'max_attempts' => $this->max_attempts,
            'shuffle_questions' => $this->shuffle_questions,
            'shuffle_answers' => $this->shuffle_answers,
            'show_correct_answers' => $this->show_correct_answers,
            'is_published' => $this->is_published,
            'total_points' => $this->total_points,
            'total_questions' => $this->total_questions,
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
        ];
    }

    /**
     * Include questions in the response.
     */
    public function withQuestions(): self
    {
        $this->load('questions.answers');
        return $this;
    }
}
