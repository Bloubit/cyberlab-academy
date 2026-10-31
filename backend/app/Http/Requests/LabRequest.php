<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:course_categories,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard,insane',
            'lab_type' => 'required|in:challenge,room,box,assessment',
            'estimated_time_minutes' => 'nullable|integer|min:1',
            'points' => 'nullable|integer|min:1',
            'os_type' => 'nullable|in:linux,windows,macos,multi',
            'tools_required' => 'nullable|array',
            'hints' => 'nullable|array',
            'learning_objectives' => 'nullable|array',
            'requires_subscription' => 'boolean',
        ];
    }
}
