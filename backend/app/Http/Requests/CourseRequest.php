<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->route('course')?->id;

        return [
            'category_id' => 'required|exists:course_categories,id',
            'title' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            'difficulty_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'estimated_hours' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'is_free' => 'boolean',
            'requirements' => 'nullable|array',
            'learning_outcomes' => 'nullable|array',
            'tags' => 'nullable|array',
        ];
    }
}
